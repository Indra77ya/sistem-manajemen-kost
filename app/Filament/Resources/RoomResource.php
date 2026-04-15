<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Room;
use App\Models\RoomImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Room Details')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('room_number')
                            ->required(),
                        Forms\Components\TextInput::make('type')
                            ->placeholder('e.g. Deluxe, Standard')
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\Select::make('billing_type')
                            ->options([
                                'per_room' => 'Per Room',
                                'per_person' => 'Per Person',
                            ])
                            ->default('per_room')
                            ->required(),
                        Forms\Components\Toggle::make('is_available')
                            ->default(true)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Facilities & Images')
                    ->schema([
                        Forms\Components\Select::make('facilities')
                            ->multiple()
                            ->relationship('facilities', 'name')
                            ->preload(),
                        Forms\Components\FileUpload::make('room_images')
                            ->multiple()
                            ->image()
                            ->directory('rooms')
                            ->reorderable()
                            ->appendFiles()
                            ->relationship('images', 'image_path')
                            ->saveRelationshipsUsing(function (Room $record, $state) {
                                $currentImages = $record->images->pluck('image_path')->toArray();

                                // Images to delete
                                $toDelete = array_diff($currentImages, $state);
                                foreach ($toDelete as $path) {
                                    Storage::disk('public')->delete($path);
                                    $record->images()->where('image_path', $path)->delete();
                                }

                                // Images to add
                                $toAdd = array_diff($state, $currentImages);
                                foreach ($toAdd as $path) {
                                    $record->images()->create(['image_path' => $path]);
                                }
                            }),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('room_number')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch')
                    ->relationship('branch', 'name'),
                Tables\Filters\TernaryFilter::make('is_available'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
