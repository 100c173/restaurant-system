<?php
namespace App\Filament\Resources\FoodForms;

use App\Filament\Resources\FoodForms\Pages\ManageFoodForms;
use App\Models\FoodForm;
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
use Filament\Tables\Table;
use Str;
use UnitEnum;

class FoodFormResource extends Resource
{
    protected static ?string $model = FoodForm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute             = 'name_ar';
    protected static ?string $navigationLabel                  = 'حالات الأغذية';
    protected static ?int $navigationSort                      = 2;
    protected static string|UnitEnum|null $navigationGroup = 'قاعدة بيانات الأغذية';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')
                    ->label('اسم الحالة بالعربي')
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_en')
                    ->label('اسم الحالة بالإنكليزي')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $name = $get('name_en');
                        if ($name) {
                            $state = $set('code', Str::slug($name));
                        }
                    })
                    ->maxLength(255),

                TextInput::make('group')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('نشط')
                    ->default('true'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_ar')
            ->columns([
                TextColumn::make('id'),
                
                TextColumn::make('name_ar')
                    ->label('الحالة')
                    ->searchable(),

                TextColumn::make('code')
                    ->weight('bold')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('فعال'),
            ])
            ->filters([
                //
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
            'index' => ManageFoodForms::route('/'),
        ];
    }
}
