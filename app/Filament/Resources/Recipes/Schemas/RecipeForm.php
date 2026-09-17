<?php
namespace App\Filament\Resources\Recipes\Schemas;

use App\Enums\RecipeStatus;
use App\Models\FoodSourceRecord;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الوصفة')
                ->columns(2)
                ->schema([

                    Select::make('food_source_record_id')
                        ->label('سجل المصدر (الطبق المرتبط)')
                        ->relationship('sourceRecord', 'id')
                        ->getOptionLabelFromRecordUsing(fn(FoodSourceRecord $sr) => sprintf(
                            '%s — %s (%s)',
                            $sr->food?->name_ar,
                            $sr->source_type->getLabel(),
                            $sr->country->getLabel() ?? '—',
                        ))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn() => request()->query('food_source_record_id'))
                        ->disabledOn('edit'),

                    TextInput::make('servings')
                        ->label('عدد الحصص')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    TextInput::make('weight_before_cooking_g')
                        ->label('الوزن قبل الطهي (غ)')
                        ->numeric(),

                    TextInput::make('weight_after_cooking_g')
                        ->label('الوزن بعد الطهي (غ)')
                        ->numeric(),

                    Select::make('status')
                        ->label('الحالة')
                        ->options(collect(RecipeStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->name]))
                        ->default(RecipeStatus::DRAFT)
                        ->required(),
                ]),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->columnSpanFull(),
        ]);
    }
}
