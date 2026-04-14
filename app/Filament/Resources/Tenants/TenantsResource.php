<?php

namespace App\Filament\Resources\Tenants;

use App\Filament\Resources\Tenants\Pages\ManageTenants;
use App\Models\Tenant;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TenantsResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = "Settings";


    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('owner_id')
                    ->label('Owner ID')
                    ->numeric()
                    ->required(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Textarea::make('data')
                    ->label('Data')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->actions([
                // 1. "Owner Info" action — read-only infolist modal
                Action::make('viewOwner')
                    ->label('Owner Info')
                    ->icon('heroicon-o-user')
                    ->color('info')
                    ->modal()
                    ->modalHeading('Owner Information')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist(fn(Tenant $record): array => [
                        TextEntry::make('owner.name')
                            ->label('Name')
                            ->default('—'),

                        TextEntry::make('owner.email')
                            ->label('Email')
                            ->copyable()
                            ->default('—'),

                        TextEntry::make('owner.id')
                            ->label('User ID'),
                    ])
                    ->record(fn(Tenant $record) => $record),

                // 2. "Domain" action — editable form modal
                Action::make('editDomain')
                    ->label('Domain')
                    ->icon('heroicon-o-globe-alt')
                    ->color('warning')
                    ->modal()
                    ->modalHeading('Edit Domain')
                    ->modalSubmitActionLabel('Save')
                    ->fillForm(fn(Tenant $record): array => [
                        'domain_name' => $record->domain?->domain,
                    ])
                    ->schema([
                        TextInput::make('domain_name')
                            ->label('Domain Name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (Tenant $record, array $data): void {
                        if ($record->domain) {
                            $record->domain->update(['domain' => $data['domain_name']]);
                        } else {
                            $record->domain()->create(['domain' => $data['domain_name']]);
                        }
                    })
                    ->successNotificationTitle('Domain updated'),

                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTenants::route('/'),
        ];
    }
}
