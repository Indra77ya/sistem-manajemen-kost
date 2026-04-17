<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    protected static ?string $label = 'Pengeluaran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required()
                    ->default(fn () => Auth::user()->branches()->first()?->id)
                    ->disabled(!Auth::user()->isDeveloper() && !Auth::user()->isOwner())
                    ->dehydrated(),
                Forms\Components\Select::make('expense_category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('title')
                    ->label('Judul Pengeluaran')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->label('Nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),
                Forms\Components\FileUpload::make('attachment')
                    ->label('Lampiran (Nota/Kuitansi)')
                    ->directory('expenses')
                    ->image(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('maintenance_request_id')
                    ->relationship('maintenanceRequest', 'title')
                    ->label('Terkait Komplain (Opsional)')
                    ->placeholder('Pilih jika terkait komplain')
                    ->disabled()
                    ->dehydrated(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->sortable()
                    ->hidden(!Auth::user()->isDeveloper() && !Auth::user()->isOwner()),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('attachment')
                    ->label('Nota')
                    ->icon(fn ($state) => $state ? 'heroicon-o-paper-clip' : null)
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Cabang')
                    ->hidden(!Auth::user()->isDeveloper() && !Auth::user()->isOwner()),
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori'),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($query, $date) => $query->whereDate('date', '>=', $date))
                            ->when($data['until'], fn ($query, $date) => $query->whereDate('date', '<=', $date));
                    })
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
