<?php
namespace App\Filament\Resources\MeasureUnits;

use App\Filament\Resources\MeasureUnits\Pages\ManageMeasureUnits;
use App\Models\MeasureUnit;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MeasureUnitResource extends Resource
{
    protected static ?string $model = MeasureUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute             = 'name_ar';
    protected static ?string $navigationLabel                  = 'وحدات القياس';
    protected static ?int $navigationSort                      = 2;
    protected static string|UnitEnum|null $navigationGroup = 'قاعدة بيانات الأغذية';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name_ar')
                    ->required()
                    ->label('الاسم بالعربي')
                    ->maxLength(255),
                TextInput::make('name_en')
                    ->required()
                    ->label('الاسم بالإنكليزي')
                    ->maxLength(255),
                TextInput::make('dimension')
                    ->required()
                    ->label('البعد')
                    ->maxLength(255),
                TextInput::make('base_factor')
                    ->numeric(),
                TextInput::make('code')
                    ->required(),
                FileUpload::make('img')
                    ->label('الصورة')
                    ->image()
                    ->disk('public')
                    ->directory('measure_unit'),
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
                TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable(),

                TextColumn::make('dimension')
                    ->label('البعد')
                    ->searchable(),

                ImageColumn::make('img')
                    ->label('الصورة')
                    ->getStateUsing(function ($record) {
                        return asset('storage/' . $record->img);
                    })
                    ->imageWidth(70)
                    ->imageHeight(70)
                    ->circular(),

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
            'index' => ManageMeasureUnits::route('/'),
        ];
    }
}
