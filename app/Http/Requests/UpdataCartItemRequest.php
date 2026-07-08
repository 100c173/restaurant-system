<?php

namespace App\Http\Requests;

use App\Models\Cart;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\Restaurant;
use Stancl\Tenancy\Facades\Tenancy;

class UpdateCartItemRequest extends FormRequest
{
    private ?Cart $cart = null;
    private ?MenuItem $menuItem = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'cart_id' => ['required', 'string', 'exists:carts,id'],
            'item_id' => ['required', 'string'],
            'variant_id' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'description' => ['nullable', 'string', 'max:255'],
            'modifier_selections' => ['nullable', 'array'],
            'modifier_selections.*.modifier_group_id' => [
                'required_with:modifier_selections',
                'integer',
            ],
            'modifier_selections.*.modifier_id' => [
                'required_with:modifier_selections',
                'integer',
            ],
        ];
    }

    /**
     * Get the "after" validation callbacks.
     */
    public function after(): array
    {
        return [
            $this->validateCartExists(...),
            $this->validateMenuItemExists(...),
            $this->validateModifierSelections(...),
        ];
    }

    /**
     * Validate that the cart exists and belongs to the authenticated user.
     */
    private function validateCartExists(Validator $validator): void
    {
        $cart = Cart::where('id', $this->cart_id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$cart) {
            $validator->errors()->add('cart_id', 'Cart not found or does not belong to you.');
            return;
        }

        $this->cart = $cart;
    }

    /**
     * Validate that the menu item exists and belongs to the cart's restaurant.
     */
    private function validateMenuItemExists(Validator $validator): void
    {
        if (!$this->cart) {
            return;
        }

        $restaurant = Restaurant::find($this->cart->tenant_id);

        if (!$restaurant) {
            $validator->errors()->add('cart_id', 'Restaurant not found.');
            return;
        }

        try {
            Tenancy::initialize($restaurant->tenant_id);

            $item = MenuItem::with('modifierGroups.modifiers')
                ->find($this->item_id);

            if (!$item) {
                $validator->errors()->add('item_id', 'Menu item not found.');
                return;
            }

            $this->menuItem = $item;

        } finally {
            Tenancy::end();
        }
    }

    /**
     * Validate modifier selections against the menu item's modifier groups.
     */
    private function validateModifierSelections(Validator $validator): void
    {
        if (!$this->menuItem || empty($this->modifier_selections)) {
            return;
        }

        $selections = collect($this->modifier_selections);

        foreach ($this->menuItem->modifierGroups as $group) {
            $selectedForGroup = $selections->where('modifier_group_id', $group->id);
            $selectedCount = $selectedForGroup->count();

            $this->validateRequiredModifiers($validator, $group, $selectedCount);
            $this->validateMaxModifiers($validator, $group, $selectedCount);
            $this->validateModifierBelongsToGroup($validator, $group, $selectedForGroup);
        }
    }

    /**
     * Validate that required modifier groups have minimum selections.
     */
    private function validateRequiredModifiers(
        Validator $validator,
        $group,
        int $selectedCount
    ): void {
        if ($group->is_required && $selectedCount < $group->min_selections) {
            $validator->errors()->add(
                'modifier_selections',
                "Please select at least {$group->min_selections} option(s) from: {$group->name}"
            );
        }
    }

    /**
     * Validate that modifier groups don't exceed maximum selections.
     */
    private function validateMaxModifiers(
        Validator $validator,
        $group,
        int $selectedCount
    ): void {
        if ($selectedCount > $group->max_selections) {
            $validator->errors()->add(
                'modifier_selections',
                "You can select at most {$group->max_selections} option(s) from: {$group->name}"
            );
        }
    }

    /**
     * Validate that selected modifiers belong to their respective groups.
     */
    private function validateModifierBelongsToGroup(
        Validator $validator,
        $group,
        Collection $selectedForGroup
    ): void {
        $validModifierIds = $group->modifiers->pluck('id')->toArray();

        foreach ($selectedForGroup as $selection) {
            if (!in_array($selection['modifier_id'], $validModifierIds, true)) {
                $validator->errors()->add(
                    'modifier_selections',
                    "Invalid option selected for: {$group->name}"
                );
            }
        }
    }

    /**
     * Handle failed validation.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['errors' => $validator->errors()], 422)
        );
    }
}