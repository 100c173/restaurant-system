<?php

namespace App\Filament\App\Pages;

use App\Services\CloudinaryUploadService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Modules\Restaurants\Models\Restaurant;

class ManageRestaurant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
    protected static ?string $navigationLabel = 'My Restaurant';
    protected static ?string $title = 'My Restaurant';
    protected static ?int $navigationSort = 1;
    protected Restaurant $restaurant;

    public function __construct()
    {
        $this->restaurant = Restaurant::where('tenant_id', tenant('id'))->firstOrFail();
    }

    public ?array $data = [];
    protected string $view = 'filament.app.pages.manage-restaurant';

    public function mount(): void
    {
        $this->form->fill($this->restaurant->toArray());
    }

    // No global header save action anymore — each section saves itself.
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Section 1: Basic Information ─────────────────────────
                Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('phone')->tel(),
                        TextInput::make('email')->email(),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                        TextInput::make('address')->required()->columnSpanFull(),
                    ])
                    ->footerActions([
                        Action::make('saveBasicInfo')
                            ->label('Save Basic Info')
                            ->action(function (Get $get) {
                                $this->restaurant->update([
                                    'name' => $get('name'),
                                    'phone' => $get('phone'),
                                    'email' => $get('email'),
                                    'description' => $get('description'),
                                    'address' => $get('address'),
                                ]);

                                Notification::make()
                                    ->title('Basic information saved successfully.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::End),

                // ── Section 2: Logo ───────────────────────────────────────
                Section::make('Logo')
                    ->schema([
                        FileUpload::make('logo')
                            ->image()
                            ->label('Logo')
                            ->required(false)
                            ->afterStateHydrated(function (FileUpload $component) {
                                if ($this->restaurant->logo) {
                                    $component->state([$this->restaurant->logo]);
                                }
                            })
                            ->getUploadedFileUsing(function (string $file): array {
                                return [
                                    'name' => basename(parse_url($file, PHP_URL_PATH)),
                                    'size' => 0,
                                    'type' => 'image/jpeg',
                                    'url' => $file,
                                ];
                            })
                            ->dehydrated(false),
                    ])
                    ->footerActions([
                        Action::make('saveLogo')
                            ->label('Save Logo')
                            ->action(function (Get $get) {
                                $uploaded = $get('logo');

                                $file = is_array($uploaded)
                                    ? collect($uploaded)->first()
                                    : $uploaded;

                                if (!$file || $file === $this->restaurant->logo) {
                                    Notification::make()
                                        ->title('No new logo selected.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                $uploader = new CloudinaryUploadService();

                                if ($this->restaurant->logo) {
                                    $uploader->delete($this->restaurant->logo);
                                }

                                $url = $uploader->upload($file->getRealPath(), 'restaurants');
                                $this->restaurant->update(['logo' => $url]);

                                Notification::make()
                                    ->title('Logo updated successfully.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::End),

                // ── Section 3: Cover Image ────────────────────────────────
                Section::make('Cover Image')
                    ->schema([
                        FileUpload::make('cover_image')
                            ->image()
                            ->label('Cover Image')
                            ->required(false)
                            ->afterStateHydrated(function (FileUpload $component) {
                                if ($this->restaurant->cover_image) {
                                    $component->state([$this->restaurant->cover_image]);
                                }
                            })
                            ->getUploadedFileUsing(function (string $file): array {
                                return [
                                    'name' => basename(parse_url($file, PHP_URL_PATH)),
                                    'size' => 0,
                                    'type' => 'image/jpeg',
                                    'url' => $file,
                                ];
                            })
                            ->dehydrated(false),
                    ])
                    ->footerActions([
                        Action::make('saveCoverImage')
                            ->label('Save Cover Image')
                            ->action(function (Get $get) {
                                $uploaded = $get('cover_image');

                                $file = is_array($uploaded)
                                    ? collect($uploaded)->first()
                                    : $uploaded;

                                if (!$file || $file === $this->restaurant->cover_image) {
                                    Notification::make()
                                        ->title('No new cover image selected.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                $uploader = new CloudinaryUploadService();

                                if ($this->restaurant->cover_image) {
                                    $uploader->delete($this->restaurant->cover_image);
                                }

                                $url = $uploader->upload($file->getRealPath(), 'restaurants');
                                $this->restaurant->update(['cover_image' => $url]);

                                Notification::make()
                                    ->title('Cover image updated successfully.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::End),

                // ── Section 4: Sham Cach Account ──────────────────────────
                Section::make('Sham Cach Account')
                    ->schema([
                        FileUpload::make('sham_cach_account_barcode')
                            ->image()
                            ->label('Sham Cach Account')
                            ->required(false)
                            ->afterStateHydrated(function (FileUpload $component) {
                                if ($this->restaurant->sham_cach_account_barcode) {
                                    $component->state([$this->restaurant->sham_cach_account_barcode]);
                                }
                            })
                            ->getUploadedFileUsing(function (string $file): array {
                                return [
                                    'name' => basename(parse_url($file, PHP_URL_PATH)),
                                    'size' => 0,
                                    'type' => 'image/jpeg',
                                    'url' => $file,
                                ];
                            })
                            ->dehydrated(false),

                        TextInput::make('sham_cach_account_id')
                            ->label('Account ID')
                            ->columnSpanFull()
                            ->copyable(),
                    ])
                    ->footerActions([
                        Action::make('saveShamCachAccount')
                            ->label('Save Sham Cach Account')
                            ->action(function (Get $get) {
                                $uploaded = $get('sham_cach_account_barcode');
                                $sham_cach_account_id = $get('sham_cach_account_id');

                                $file = is_array($uploaded)
                                    ? collect($uploaded)->first()
                                    : $uploaded;

                                if (
                                    (!$file || $file === $this->restaurant->sham_cach_account_barcode)
                                    && $sham_cach_account_id === $this->restaurant->sham_cach_account_id
                                ) {
                                    Notification::make()
                                        ->title('No changes detected.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                $uploader = new CloudinaryUploadService();

                                if ($file && $file !== $this->restaurant->sham_cach_account_barcode) {
                                    if ($this->restaurant->sham_cach_account_barcode) {
                                        $uploader->delete($this->restaurant->sham_cach_account_barcode);
                                    }

                                    $url = $uploader->upload($file->getRealPath(), 'shamCash');
                                    $this->restaurant->sham_cach_account_barcode = $url;
                                }

                                $this->restaurant->sham_cach_account_id = $sham_cach_account_id;
                                $this->restaurant->save();

                                Notification::make()
                                    ->title('Sham Cach Account updated successfully.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::End),

                // ── Section 5: Working Hours ──────────────────────────────
                Section::make('Working Hours')
                    ->columns(2)
                    ->schema([
                        TimePicker::make('opening_time'),
                        TimePicker::make('closing_time'),
                        Toggle::make('is_active')->label('Active'),
                        Toggle::make('has_delivery')->label('Delivery'),
                    ])
                    ->footerActions([
                        Action::make('saveWorkingHours')
                            ->label('Save Working Hours')
                            ->action(function (Get $get) {
                                $this->restaurant->update([
                                    'opening_time' => $get('opening_time'),
                                    'closing_time' => $get('closing_time'),
                                    'is_active' => $get('is_active'),
                                    'has_delivery' => $get('has_delivery'),
                                ]);

                                Notification::make()
                                    ->title('Working hours saved successfully.')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->footerActionsAlignment(Alignment::End),
            ])
            ->statePath('data');
    }
}
