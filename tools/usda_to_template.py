#!/usr/bin/env python3
"""
usda_to_template.py (v2) - USDA FoodData Central bulk CSVs -> NutriLink import bundle.
Works from local files only: no API key, no rate limit.

Setup (one time)
  1. Download the CSV zips from https://fdc.nal.usda.gov/download-datasets
     (Foundation Foods and SR Legacy; skip Branded). Each zip contains files with the SAME
     names (food.csv, food_nutrient.csv ...), so unzip each into its OWN folder, e.g.
     ./fdc/sr_legacy and ./fdc/foundation, and run the script once per folder
     (own crosswalk, own --out).
  2. Point --nutrients / --source-codes at database/seeders/data/nutrients.csv and
     nutrient_source_codes.csv (or at a DB export with the same columns).

Commands
  verify-map   check every mapped FDC nutrient id against the real nutrient.csv (run this first)
  search       list candidate FDC foods to fill the crosswalk
  build        crosswalk -> records.csv, nutrient_values.csv, portions.csv
  template     empty hand-entry header (record columns + nutrient codes)

Crosswalk (usda_crosswalk.csv) - the one human decision per USDA record:
  fdc_id, food_form_code, food_id OR food_name_ar, [food_name_en, category_path]
"""
import argparse
import re
import sys
from pathlib import Path

import pandas as pd

SOURCE_SYSTEM = "usda_fdc"
DATASETS = {  # FDC food.csv data_type -> data_sources.code
    "foundation_food": "usda_fdc_foundation",
    "sr_legacy_food": "usda_fdc_sr_legacy",
    "survey_fndds_food": "usda_fdc_fndds",
}
UNIT_ALIASES = {"µg": "ug", "μg": "ug", "mcg": "ug"}
RECORD_COLS = [
    "food_id", "food_name_ar", "food_name_en", "category_path", "food_form_code",
    "data_source_code", "external_ref", "notes",
]


def norm_unit(u) -> str:
    u = str(u).strip().lower()
    return UNIT_ALIASES.get(u, u)


def squash(s) -> str:
    return re.sub(r"[^a-z0-9]", "", str(s).lower())


def read(folder, name, required=False, **kw):
    path = Path(folder) / name
    if not path.exists():
        if required:
            sys.exit(f"Missing {path}")
        return None
    return pd.read_csv(path, dtype=str, encoding="utf-8-sig", **kw)


def read_codes(path) -> pd.DataFrame:
    codes = pd.read_csv(path, dtype=str, encoding="utf-8-sig")
    return codes[codes.source_system == SOURCE_SYSTEM].copy()


# --------------------------------------------------------------------- verify-map
def cmd_verify_map(a):
    fdc = read(a.fdc_dir, "nutrient.csv", required=True)
    info = fdc.set_index("id")[["name", "unit_name"]].to_dict("index")
    bad = 0
    for r in read_codes(a.source_codes).itertuples():
        got = info.get(r.code)
        if got is None:
            print(f"MISSING  {r.code:>5} {r.nutrient_code}: id not in this download's nutrient.csv")
            bad += 1
            continue
        if float(r.factor) == 1.0 and norm_unit(got["unit_name"]) != norm_unit(r.source_unit):
            print(f"ERROR    {r.code:>5} {r.nutrient_code}: unit {got['unit_name']!r}, seed expects {r.source_unit!r}")
            bad += 1
            continue
        a_, b_ = squash(r.source_name), squash(got["name"])
        status = "OK      " if (a_ in b_ or b_ in a_) else "REVIEW  "
        if status.strip() == "REVIEW":
            print(f"{status} {r.code:>5} {r.nutrient_code}: FDC calls it {got['name']!r}, seed says {r.source_name!r}")
    print("verify-map:", "problems found" if bad else "no missing ids / unit errors (REVIEW lines above are name differences only)")
    sys.exit(1 if bad else 0)


# --------------------------------------------------------------------- search
def cmd_search(a):
    food = read(a.fdc_dir, "food.csv", required=True,
                usecols=["fdc_id", "data_type", "description", "publication_date"])
    food = food[food.data_type.isin(DATASETS)]
    mask = pd.Series(True, index=food.index)
    for term in a.terms:
        mask &= food.description.str.contains(term, case=False, regex=False, na=False)
    order = {"foundation_food": 0, "sr_legacy_food": 1, "survey_fndds_food": 2}
    hits = food[mask].assign(_o=lambda d: d.data_type.map(order)).sort_values(["_o", "description"])
    if hits.empty:
        sys.exit("No matches in Foundation / SR Legacy / FNDDS.")
    print(hits.drop(columns="_o").head(a.limit).to_string(index=False, max_colwidth=90))
    if len(hits) > a.limit:
        print(f"... {len(hits) - a.limit} more; add terms or raise --limit")


# --------------------------------------------------------------------- build
def load_nutrient_map(a, fdc_nutrient) -> pd.DataFrame:
    nut = pd.read_csv(a.nutrients, dtype=str, encoding="utf-8-sig").rename(columns={"code": "nutrient_code"})
    keep = ["nutrient_code", "unit"] + (["is_core"] if "is_core" in nut.columns else [])
    m = read_codes(a.source_codes).merge(nut[keep], on="nutrient_code", how="left")
    if m.unit.isna().any():
        sys.exit(f"nutrient_source_codes references unknown nutrients: {m[m.unit.isna()].nutrient_code.tolist()}")
    m["factor"] = pd.to_numeric(m.factor).fillna(1.0)
    m["priority"] = pd.to_numeric(m.priority).fillna(100).astype(int)

    fdc_unit = dict(zip(fdc_nutrient.id, fdc_nutrient.unit_name.map(norm_unit)))
    unit_bad = m.apply(lambda r: r.factor == 1.0 and fdc_unit.get(r.code) != norm_unit(r.unit), axis=1)
    for r in m[unit_bad].itertuples():
        print(f"WARNING unit mismatch or unknown id, skipped: {r.nutrient_code} <- FDC {r.code} "
              f"(ours {r.unit!r}, FDC {fdc_unit.get(r.code)!r})", file=sys.stderr)
    return m[~unit_bad]


def cmd_build(a):
    out = Path(a.out)
    out.mkdir(parents=True, exist_ok=True)

    cw = pd.read_csv(a.crosswalk, dtype=str, encoding="utf-8-sig").fillna("")
    for col in ("fdc_id", "food_form_code"):
        if col not in cw.columns:
            sys.exit(f"Crosswalk needs a '{col}' column")
    for col in ("food_id", "food_name_ar", "food_name_en", "category_path"):
        if col not in cw.columns:
            cw[col] = ""
    bad = cw[(cw.food_id == "") & (cw.food_name_ar == "")]
    if not bad.empty:
        sys.exit(f"Crosswalk rows without food_id or food_name_ar: fdc_id {bad.fdc_id.tolist()}")
    if cw.fdc_id.duplicated().any():
        sys.exit(f"Duplicate fdc_id in crosswalk: {cw.fdc_id[cw.fdc_id.duplicated()].tolist()}")
    wanted = set(cw.fdc_id)

    # ---- records ----
    food = read(a.fdc_dir, "food.csv", required=True,
                usecols=["fdc_id", "data_type", "description", "publication_date"])
    food = food[food.fdc_id.isin(wanted)]
    missing = wanted - set(food.fdc_id)
    if missing:
        sys.exit(f"fdc_id not found in food.csv: {sorted(missing)}")
    unsupported = food[~food.data_type.isin(DATASETS)]
    if not unsupported.empty:
        sys.exit("Only Foundation / SR Legacy / FNDDS are accepted. Offending: "
                 f"{unsupported[['fdc_id', 'data_type']].values.tolist()}")

    rec = cw.merge(food, on="fdc_id")
    rec_out = pd.DataFrame({
        "food_id": rec.food_id, "food_name_ar": rec.food_name_ar,
        "food_name_en": rec.food_name_en, "category_path": rec.category_path,
        "food_form_code": rec.food_form_code,
        "data_source_code": rec.data_type.map(DATASETS),
        "external_ref": rec.fdc_id,
        "notes": "FDC: " + rec.description.fillna("") + " (published " + rec.publication_date.fillna("?") + ")",
    })[RECORD_COLS]
    rec_out.to_csv(out / "records.csv", index=False)
    ds_of = dict(zip(rec_out.external_ref, rec_out.data_source_code))

    # ---- nutrient values ----
    fdc_nutrient = read(a.fdc_dir, "nutrient.csv", required=True)
    nmap = load_nutrient_map(a, fdc_nutrient)

    path = Path(a.fdc_dir) / "food_nutrient.csv"
    header = pd.read_csv(path, nrows=0).columns
    cols = ["fdc_id", "nutrient_id", "amount", "data_points", "derivation_id", "min", "max"]
    chunks = [
        ch[ch.fdc_id.isin(wanted) & ch.nutrient_id.isin(set(nmap.code))]
        for ch in pd.read_csv(path, dtype=str, usecols=[c for c in cols if c in header], chunksize=500_000)
    ]
    vals = pd.concat(chunks).reindex(columns=cols)
    vals["amount"] = pd.to_numeric(vals.amount, errors="coerce")
    vals = vals.dropna(subset=["amount"])
    if (vals.amount < 0).any():
        sys.exit("Negative nutrient amounts found - inspect the source data before importing.")

    vals = vals.merge(nmap[["code", "nutrient_code", "unit", "factor", "priority"]],
                      left_on="nutrient_id", right_on="code")
    vals["amount"] = (vals.amount * vals.factor).round(6)
    before = len(vals)
    vals["_row"] = range(len(vals))  # tie-break = source file order, so results never depend on sort internals
    vals = (vals.sort_values(["fdc_id", "nutrient_code", "priority", "_row"])
                .drop_duplicates(["fdc_id", "nutrient_code"], keep="first"))
    if before != len(vals):
        print(f"note: {before - len(vals)} value(s) had several source codes for one nutrient; "
              "kept the lowest priority number.", file=sys.stderr)

    der = read(a.fdc_dir, "food_nutrient_derivation.csv")
    der_map = {}
    if der is not None:
        der = der.fillna("")
        der_map = dict(zip(der.id, der.code + ": " + der.description))
    nv = pd.DataFrame({
        "data_source_code": vals.fdc_id.map(ds_of), "external_ref": vals.fdc_id,
        "nutrient_code": vals.nutrient_code, "amount_per_100g": vals.amount, "unit": vals.unit,
        "method": "imported",  # USDA derivation text is kept in notes, not guessed into our enum
        "sample_count": vals.data_points, "min_amount": vals["min"], "max_amount": vals["max"],
        "notes": vals.derivation_id.map(der_map).fillna(""),
    })
    nv.to_csv(out / "nutrient_values.csv", index=False)

    # ---- portions ----
    por_n = 0
    por = read(a.fdc_dir, "food_portion.csv")
    if por is not None:
        mu = read(a.fdc_dir, "measure_unit.csv")
        mu_name = dict(zip(mu.id, mu.name)) if mu is not None else {}
        por = por[por.fdc_id.isin(wanted)].copy()
        por["gram_weight"] = pd.to_numeric(por.gram_weight, errors="coerce")
        por = por.dropna(subset=["gram_weight"])
        unit = por.measure_unit_id.map(mu_name)
        usable = unit.notna() & (unit.str.lower() != "undetermined")
        label = unit.where(usable, por.get("modifier")).fillna(por.get("portion_description"))
        por_out = pd.DataFrame({
            "data_source_code": por.fdc_id.map(ds_of), "external_ref": por.fdc_id,
            "measure_unit_label": label,  # resolved through measure_unit_aliases
            "amount": pd.to_numeric(por.get("amount"), errors="coerce").fillna(1),
            "gram_weight": por.gram_weight,
            "description": por.get("portion_description"),
            "modifier": por.get("modifier"), "basis": "usda",
        })
        por_out.to_csv(out / "portions.csv", index=False)
        por_n = len(por_out)

    # ---- quality report ----
    print(f"records: {len(rec_out)} | nutrient values: {len(nv)} | portions: {por_n} -> {out}/")
    if "is_core" in nmap.columns:
        core = set(nmap[nmap.is_core.isin(["1", "true", "True"])].nutrient_code)
        have = nv.groupby("external_ref").nutrient_code.agg(set)
        for fid in sorted(wanted):
            gap = core - have.get(fid, set())
            if gap:
                print(f"  fdc_id {fid}: missing core nutrients {sorted(gap)}")


def cmd_template(a):
    nut = pd.read_csv(a.nutrients, dtype=str, encoding="utf-8-sig")
    pd.DataFrame(columns=RECORD_COLS + nut.code.tolist()).to_csv(a.out, index=False)
    print(f"wrote {a.out}")


def main():
    p = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    sub = p.add_subparsers(dest="cmd", required=True)

    v = sub.add_parser("verify-map")
    v.add_argument("--fdc-dir", required=True)
    v.add_argument("--source-codes", required=True)
    v.set_defaults(fn=cmd_verify_map)

    s = sub.add_parser("search")
    s.add_argument("terms", nargs="+")
    s.add_argument("--fdc-dir", required=True)
    s.add_argument("--limit", type=int, default=40)
    s.set_defaults(fn=cmd_search)

    b = sub.add_parser("build")
    b.add_argument("--fdc-dir", required=True)
    b.add_argument("--nutrients", required=True)
    b.add_argument("--source-codes", required=True)
    b.add_argument("--crosswalk", required=True)
    b.add_argument("--out", default="import_bundle")
    b.set_defaults(fn=cmd_build)

    t = sub.add_parser("template")
    t.add_argument("--nutrients", required=True)
    t.add_argument("--out", default="hand_entry_template.csv")
    t.set_defaults(fn=cmd_template)

    a = p.parse_args()
    a.fn(a)


if __name__ == "__main__":
    main()
