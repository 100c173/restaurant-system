<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Restaurants\Models\MenuItem;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'item_id' => ['required', 'integer'],
            'variant_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'special_note' => ['nullable', 'string', 'max:255'],
            'modifier_selections' => ['nullable', 'array'],
            'modifier_selections.*.modifier_group_id' => ['required_with:modifier_selections', 'integer'],
            'modifier_selections.*.modifier_id' => ['required_with:modifier_selections', 'integer'],
        ];
    }
    public function after(): array
    {
        return [
            function ($validator) {
                // Load the item with its modifier groups from the tenant DB
                // You'll need to resolve the tenant connection here
                $item = MenuItem::with('modifierGroups.modifiers')
                    ->find($this->item_id);

                if (!$item) {
                    $validator->errors()->add('item_id', 'Item not found.');
                    return;
                }

                $selections = collect($this->modifier_selections ?? []);

                foreach ($item->modifierGroups as $group) {
                    // Count how many modifiers were selected for this group
                    $selectedForGroup = $selections->where('modifier_group_id', $group->id);
                    $count = $selectedForGroup->count();

                    // Check required groups
                    if ($group->is_required && $count < $group->min_selections) {
                        $validator->errors()->add(
                            'modifier_selections',
                            "يجب اختيار خيار من: {$group->name}"
                        );
                    }

                    // Check max selections
                    if ($count > $group->max_selections) {
                        $validator->errors()->add(
                            'modifier_selections',
                            "تجاوزت الحد المسموح في: {$group->name}"
                        );
                    }

                    // Check that selected modifier IDs actually belong to this group
                    $validModifierIds = $group->modifiers->pluck('id');
                    foreach ($selectedForGroup as $sel) {
                        if (!$validModifierIds->contains($sel['modifier_id'])) {
                            $validator->errors()->add(
                                'modifier_selections',
                                "الخيار المحدد غير صالح في: {$group->name}"
                            );
                        }
                    }
                }
            }
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
