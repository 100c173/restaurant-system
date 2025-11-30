<?php

namespace App\Filament\Owner\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Order Details')
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false)  // لا يُحدّث القيمة في قاعدة البيانات
                            ->required(),

                        Select::make('restaurant_id')
                            ->label('Restaurant')
                            ->relationship('restaurant', 'name')
                            ->searchable()
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false)  // لا يُحدّث القيمة في قاعدة البيانات
                            ->required(),

                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->unique(ignoreRecord: true)
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false)  // لا يُحدّث القيمة في قاعدة البيانات
                            ->required()
                            ->maxLength(50),

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false)  // لا يُحدّث القيمة في قاعدة البيانات
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'   => 'Pending',
                                'preparing' => 'Preparing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->live() // لجعل الفورم يتفاعل مباشرة
                            ->required(),

                        Textarea::make('cancel_reason')
                            ->label('Cancellation Reason')
                            ->visible(fn(Get $get) => $get('status') === 'cancelled')
                            ->requiredIf('status', 'cancelled'),

                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'paid'   => 'Paid',
                                'pending' => 'Pending',

                            ])
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false),  // لا يُحدّث القيمة في قاعدة البيانات


                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Card',
                                'online' => 'Online',
                            ])
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false) , // لا يُحدّث القيمة في قاعدة البيانات

                    ]),

                Section::make('Delivery & Notes')
                    ->columns(2)
                    ->schema([
                        Textarea::make('delivery_address')
                            ->label('Delivery Address')
                            ->rows(2)
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false) , // لا يُحدّث القيمة في قاعدة البيانات


                        Textarea::make('customer_notes')
                            ->label('Customer Notes')
                            ->rows(2)
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false)  // لا يُحدّث القيمة في قاعدة البيانات
                            ->nullable(),

                        TextInput::make('preparation_time')
                            ->label('Preparation Time (minutes)')
                            ->numeric()
                            ->minValue(0)
                            ->requiredIf('status', 'accepted'),

                        /*
                        Select::make('assigned_driver_id')
                            ->label('Assigned Driver')
                            ->relationship('assignedDriver', 'name')
                            ->searchable()
                            ->nullable(), */
                    ]),

                Section::make('Fees & Tax')
                    ->columns(2)
                    ->schema([
                        TextInput::make('delivery_fee')
                            ->label('Delivery Fee')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false)  // لا يُحدّث القيمة في قاعدة البيانات
                            ->default(0),

                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()           // يمنع التعديل
                            ->dehydrated(false)  // لا يُحدّث القيمة في قاعدة البيانات
                            ->default(0),
                    ]),
            ]);
    }
}
