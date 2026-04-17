<?php

namespace App\Filament\App\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Restaurants\Models\Restaurant;

class ManageRestaurant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
    protected static ?string $navigationLabel = 'My Restaurant';
    protected static ?string $title = 'My Restaurant';
    protected static ?int $navigationSort = 1;
    protected Restaurant $restaurant ;

    public function __construct(){
        $this->restaurant = Restaurant::where('owner_id', auth()->id())->firstOrFail();
    }

    public ?array $data = [];
    protected string $view = 'filament.app.pages.manage-restaurant';
    public function mount(): void
    {

        $this->form->fill($this->restaurant->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel(),
                        TextInput::make('email')
                            ->email(),
                        Textarea::make('description')
                            ->rows(3),
                        TextInput::make('address')
                            ->required(),
                    ])->columns(2),

                Section::make('Working Hours')
                    ->schema([
                        TimePicker::make('opening_time'),
                        TimePicker::make('closing_time'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }


    public function save(): void
    {
        $this->restaurant->update($this->form->getState());

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->action('save'),
        ];
    }
}
