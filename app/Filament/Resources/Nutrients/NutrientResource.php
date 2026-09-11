<?php
namespace App\Filament\Resources\Nutrients;

use App\Filament\Resources\Nutrients\Pages\ManageNutrients;
use App\Models\Nutrient;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Str;
use UnitEnum;

class NutrientResource extends Resource
{
    protected static ?string $model = Nutrient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute             = 'name_ar';
    protected static ?string $navigationLabel                  = 'العناصر الغذائية';
    protected static string|UnitEnum|null $navigationGroup = 'قاعدة بيانات الأغذية';
    protected static ?int $navigationSort                      = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')
                    ->label(static::$recordTitleAttribute)
                    ->label('الاسم بالعربي')
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_en')
                    ->label('الاسم بالإنكليزي')
                    ->required()
                    ->maxLength(255)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $unit = $get('unit');
                        if ($unit) {
                            $state = $set('code', Str::slug($state . '_' . $unit));
                        }
                    }),

                TextInput::make('unit')
                    ->label('الوحدة')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $name_en = $get('name_en');
                        if ($name_en) {
                            $state = $set('code', Str::slug($name_en . '_' . $state));
                        }
                    }),

                TextInput::make('usda_nutrient_id')
                    ->label('USDA Nutrient ID'),
                TextInput::make('code')
                    ->label('Code')
                    ->required()
                    ->maxLength(255),

                TextInput::make('display_order')
                    ->label('ترتيب عرضه بالتحليل')
                    ->numeric()
                    ->default(1),

                Toggle::make('is_core')
                    ->label('عنصر أساسي ؟')
                    ->default(true),

                Toggle::make('is_active')
                    ->label('نشط ؟')
                    ->default(true),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_ar')
            ->columns([
                TextColumn::make('name_ar')
                    ->label('العنصر الغذائي')
                    ->searchable(),

                TextColumn::make('unit')
                    ->label('وحدة القياس')
                    ->searchable(),

                TextColumn::make('code')
                    ->weight('bold')
                    ->searchable(),

                IconColumn::make('is_core')
                    ->label('اساسي'),
            ])
            ->filters([
                SelectFilter::make('is_core')
                    ->label('اساسي')
                    ->options([
                        '1' => 'اساسي',
                        '0' => 'غير اساسي',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageNutrients::route('/'),
        ];
    }
}
