<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoardingHouseResource\Pages;
use App\Filament\Resources\BoardingHouseResource\RelationManagers;
use App\Models\BoardingHouse;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class BoardingHouseResource extends Resource
{
    protected static ?string $model = BoardingHouse::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Boarding House Management';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('Informasi Umum')
                    ->schema([
                        FileUpload::make('thumbnail')->image()->directory('boarding_houses')->required(),
                        TextInput::make('name')->required()
                        ->debounce(500) //delay
                        ->reactive() //untuk mengubah state lain di slug
                        ->afterStateUpdated(function($state, callable $set) {
                            $set('slug', Str::slug($state));
                        }),
                        TextInput::make('slug')->required(),
                        Select::make('city_id')
                        ->relationship('city', 'name')
                        ->required(),
                        Select::make('category_id')
                        ->relationship('category', 'name')
                        ->required(),
                        RichEditor::make('description')->required(),
                        TextInput::make('price')
                        ->numeric()
                        ->prefix('Rp. ')
                        ->required(),
                        Textarea::make('address')
                        ->required()
                    ]),
                    Tabs\Tab::make('Bonus')
                    ->schema([
                        Repeater::make('bonuses')
                        ->relationship('bonuses')
                        ->schema([
                            FileUpload::make('image')->image()->directory('bonus')->required(),
                            TextInput::make('name')->required(),
                            TextInput::make('description')->required(),
                        ])
                    ]),
                    Tabs\Tab::make('kamar')
                    ->schema([
                        Repeater::make('rooms')
                        ->relationship('rooms')
                        ->schema([
                            TextInput::make('name')->required(),
                            TextInput::make('room_type')->required(),
                            TextInput::make('square_feet')
                            ->numeric()
                            ->required(),
                            TextInput::make('capacity')
                            ->numeric()
                            ->required(),
                            TextInput::make('price_per_month')
                            ->numeric()
                            ->prefix('Rp. ')
                            ->required(),
                            Toggle::make('is_available')
                            ->required(),
                            Repeater::make('images')
                            ->relationship('images')
                            ->schema([
                                FileUpload::make('image')
                                ->image()
                                ->directory('rooms')
                                ->required(),
                            ])
                        ])
                    ]),
                ])->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('city.name'),
                TextColumn::make('category.name'),
                TextColumn::make('price'),
                ImageColumn::make('thumbnail')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListBoardingHouses::route('/'),
            'create' => Pages\CreateBoardingHouse::route('/create'),
            'edit' => Pages\EditBoardingHouse::route('/{record}/edit'),
        ];
    }
}
