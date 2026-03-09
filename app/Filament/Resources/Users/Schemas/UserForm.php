<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                    
                DateTimePicker::make('email_verified_at'),
                
                TextInput::make('password')
                    ->password(),
                
                Select::make('permissions')
                    ->label('Permissions')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->searchable()
                    ->preload(),

                Select::make('roles')
                    ->label('roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
