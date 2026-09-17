<?php
namespace App\Filament\Resources\Food\Resources\FoodSourceRecords\Schemas;

use App\Enums\ConfidenceLevel;
use App\Enums\Country;
use App\Enums\FoodSourceStatus;
use App\Enums\FoodSourceType;
use App\Enums\NutrientValueMethod;
use App\Models\Nutrient;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FoodSourceRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات المصدر')
                ->columns(2)
                ->schema([
                    Select::make('source_type')
                        ->label('نوع المصدر')
                        ->options(FoodSourceType::class) // now clean — enum implements HasLabel
                        ->required()
                        ->live(),

                    Select::make('country')
                        ->label('بلد المصدر')
                        ->options(Country::class)
                        ->searchable()
                        ->helperText('بلد الدراسة/المصدر الأصلي — قد يختلف عن بلد الطبق نفسه'),

                    TextInput::make('source_name')
                        ->label('اسم المصدر')
                        ->maxLength(255),

                    TextInput::make('external_ref')
                        ->label('المرجع الخارجي')
                        ->helperText(fn($get) => $get('source_type') === FoodSourceType::USDA_FDC->value
                                ? 'أدخل رقم fdc_id من قاعدة بيانات USDA'
                                : 'رابط أو رقم مرجعي للدراسة أو المصدر')
                        ->maxLength(128),

                    TextInput::make('data_type')
                        ->label('نوع البيانات')
                        ->maxLength(64),

                    Select::make('food_form_id')
                        ->label('حالة الغذاء في هذا المصدر')
                        ->relationship('foodForm', 'name_ar')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name_ar')->label('الاسم بالعربي')->required(),
                            TextInput::make('name_en')->label('الاسم بالإنكليزي'),
                            TextInput::make('group')->label('المجموعة'),
                            TextInput::make('code')->label('الرمز')->required(),
                        ]),
                ]),

            Section::make('حالة السجل وأولويته')
                ->columns(3)
                ->schema([
                    Select::make('status')
                        ->label('الحالة')
                        ->options(collect(FoodSourceStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->name]))
                        ->default(FoodSourceStatus::ACTIVE)
                        ->required(),

                    TextInput::make('priority')
                        ->label('الأولوية')
                        ->numeric()
                        ->default(100)
                        ->helperText('الرقم الأصغر له أولوية أعلى عند التعارض بين المصادر'),

                    Toggle::make('is_preferred')
                        ->label('مصدر مفضل؟')
                        ->inline(false),

                    DateTimePicker::make('valid_from')->label('صالح من'),
                    DateTimePicker::make('valid_to')->label('صالح حتى'),
                    DateTimePicker::make('imported_at')->label('تاريخ الاستيراد'),
                ]),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->columnSpanFull(),

            Section::make('القيم الغذائية لكل 100 غرام')
                ->description('يتم تعبئة كل عنصر غذائي نشط تلقائياً عند إنشاء سجل جديد')
                ->schema([
                    Repeater::make('nutrientValues')
                        ->relationship()
                        ->label('')
                        ->columns(4)
                        ->defaultItems(0)
                    /*
                    // Pre-seeds one row per active nutrient on CREATE only;
                    // on EDIT the relationship loads the real saved rows.
                        ->default(fn() => Nutrient::query()
                                ->where('is_active', true)
                                ->orderBy('display_order')
                                ->get()
                                ->map(fn(Nutrient $n) => [
                                    'nutrient_id' => $n->id,
                                    'unit'        => $n->unit,
                                ])->toArray())
                            */
                        ->schema([
                            Select::make('nutrient_id')
                                ->label('العنصر الغذائي')
                                ->relationship('nutrient', 'name_ar')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn($state, callable $set) => $set('unit', Nutrient::find($state)?->unit)),

                            TextInput::make('amount_per_100g')
                                ->label('القيمة')
                                ->numeric()
                                ->required(),

                            TextInput::make('unit')
                                ->label('الوحدة'),

                            Select::make('method')
                                ->label('طريقة القياس')
                                ->options(collect(NutrientValueMethod::cases())->mapWithKeys(fn($c) => [$c->value => $c->name])),

                            Select::make('confidence_level')
                                ->label('مستوى الثقة')
                                ->options(collect(ConfidenceLevel::cases())->mapWithKeys(fn($c) => [$c->value => $c->name]))
                                ->default(ConfidenceLevel::REFERENCE),
                        ])
                        ->itemLabel(fn(array $state): ?string => Nutrient::find($state['nutrient_id'] ?? null)?->name_ar)
                        ->collapsible(),
                ]),
        ]);
    }
}
