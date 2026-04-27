<?php

namespace App\Filament\Resources\ShamCashAccounts;

use App\Filament\Resources\ShamCashAccounts\Pages\ManageShamCashAccounts;
use App\Models\ShamCashAccount;
use App\Services\CloudinaryUploadService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ShamCashAccountsResource extends Resource
{
    protected static ?string $model = ShamCashAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'account_name';
    protected static ?int $navigationSort = 2;
    protected static string|UnitEnum|null $navigationGroup = "Settings";

    /**
     * CREATE modal — unchanged.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('account_name')
                ->maxLength(255),

            TextInput::make('account_number')
                ->maxLength(255),

            TextInput::make('code')
                ->required()
                ->maxLength(255),

            FileUpload::make('barcode_image')
                ->image()
                ->label('Barcode')
                ->required()
                ->saveUploadedFileUsing(function ($file, $record) {
                    $uploader = new CloudinaryUploadService();
                    return $uploader->upload($file->getRealPath(), 'shamCash');
                }),

            Toggle::make('is_active')->label('Active'),
        ]);
    }

    /**
     * EDIT modal — two independent sections, each with its own save button.
     */
    public static function editSchema(Schema $schema): Schema
    {
        return $schema->components([

            // ── Section 1: Basic Information ──────────────────────────────
            Section::make('Basic Information')
                ->schema([
                    TextInput::make('account_name')
                        ->maxLength(255),

                    TextInput::make('account_number')
                        ->maxLength(255)
                        ->copyable(),

                    TextInput::make('code')
                        ->required()
                        ->maxLength(255)
                        ->copyable(),

                    Toggle::make('is_active')
                        ->label('Active'),
                ])
                ->footerActions([
                    Action::make('saveBasicInfo')
                        ->label('Save Basic Info')
                        // Inject field values directly via Get — no $livewire needed.
                        ->action(function (Get $get, $record) {
                            $record->update([
                                'account_name'   => $get('account_name'),
                                'account_number' => $get('account_number'),
                                'code'           => $get('code'),
                                'is_active'      => $get('is_active'),
                            ]);

                            Notification::make()
                                ->title('Basic information saved successfully.')
                                ->success()
                                ->send();
                        }),
                ])
                ->footerActionsAlignment(Alignment::End),

            // ── Section 2: Barcode Image ──────────────────────────────────
            Section::make('Barcode Image')
                ->schema([
                    FileUpload::make('barcode_image')
                        ->image()
                        ->label('Barcode')
                        ->required(false)
                        // Seed state with the stored Cloudinary URL so the
                        // preview thumbnail appears when the modal opens.
                        ->afterStateHydrated(function (FileUpload $component, $record) {
                            if ($record?->barcode_image) {
                                $component->state([$record->barcode_image]);
                            }
                        })
                        // Tell FilePond how to render the stored URL as a preview.
                        ->getUploadedFileUsing(function (string $file): array {
                            return [
                                'name' => basename(parse_url($file, PHP_URL_PATH)),
                                'size' => 0,
                                'type' => 'image/jpeg',
                                'url'  => $file,
                            ];
                        })
                        ->dehydrated(false),
                ])
                ->footerActions([
                    Action::make('saveImage')
                        ->label('Save Image')
                        ->action(function (Get $get, $record) {
                            $uploaded = $get('barcode_image');

                            // $uploaded is an array; the first entry is either
                            // the existing URL string or a TemporaryUploadedFile.
                            $file = is_array($uploaded)
                                ? collect($uploaded)->first()
                                : $uploaded;

                            // Nothing selected, or user didn't change the image.
                            if (! $file || $file === $record->barcode_image) {
                                Notification::make()
                                    ->title('No new image selected.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $uploader = new CloudinaryUploadService();

                            if ($record->barcode_image) {
                                $uploader->delete($record->barcode_image);
                            }

                            // $file is a TemporaryUploadedFile when a new file
                            // was dropped into FilePond.
                            $url = $uploader->upload($file->getRealPath(), 'shamCash');

                            $record->update(['barcode_image' => $url]);

                            Notification::make()
                                ->title('Barcode image updated successfully.')
                                ->success()
                                ->send();
                        }),
                ])
                ->footerActionsAlignment(Alignment::End),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('account_name')
            ->columns([
                TextColumn::make('account_name')
                    ->label('Account Name')
                    ->searchable(),

                TextColumn::make('account_number')
                    ->label('Account Number')
                    ->badge()
                    ->copyable()
                    ->searchable(),

                TextColumn::make('code')
                    ->badge()
                    ->copyable(),

                ImageColumn::make('barcode_image')
                    ->label('Barcode Image')
                    ->square(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->schema(fn(Schema $schema): Schema => static::editSchema($schema))
                    // Hide the global Save button — each section has its own.
                    ->modalSubmitAction(false),

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
            'index' => ManageShamCashAccounts::route('/'),
        ];
    }
}