<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProductForm
{

    public static function generateSku(Get $get, Set $set): void
    {
        $brand = Brand::find($get('brand_id'));
        $category = Category::find($get('category_id'));
        $subcategory = SubCategory::find($get('sub_category_id'));


        if (!$category || !$subcategory || !$brand) {
            return;
        }

        //mengambil 3 Huruf Pertama & diformat capital
        $catCode = strtoupper(substr($category->name, 0, 3));
        $subcatCode = strtoupper(substr($subcategory->name, 0, 3));
        $branCode = strtoupper(substr($brand->name, 0, 3));

        $lastSku = Product::where('category_id', $category->id)
            ->where('sub_category_id', $subcategory->id)
            ->where('brand_id', $brand->id)
            ->orderBy('id', 'desc')
            ->value('sku');

        $nextNumber = 1;
        if ($lastSku) {
            $parts = explode('-', $lastSku);
            $lastNumber = intval(end($parts));
            $nextNumber = $lastNumber + 1;
        }

        $sku = sprintf('%s-%s-%s-%03d', $branCode, $catCode, $subcatCode, $nextNumber);
        $set('sku', $sku);
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make([
                        TextInput::make('name')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('base_price')
                            ->numeric()
                            ->prefix('IDR')
                            ->required(),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('IDR'),

                        TextInput::make('stock')
                            ->required()
                            ->numeric(),

                        TextInput::make('sku')
                            ->readOnly()
                            ->dehydrated(),

                        TextInput::make('barcode'),

                        Group::make([

                            Toggle::make('in_stock')
                                ->required(),

                            Toggle::make('is_active')
                                ->required(),
                        ]),

                        RichEditor::make('description')
                            ->columnSpanFull(),

                    ])->columns(3)
                        ->description('Product Detail')

                ])->columnSpan(2),

                Section::make([
                    Select::make('brand_id')
                        ->relationship('brand', 'name', fn($query) => $query->where('is_active', true))
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            static::generateSku($get, $set);
                        })
                        ->createOptionForm([
                            FileUpload::make('image'),
                            TextInput::make('name'),
                            Toggle::make('is_active'),
                        ]),

                    Select::make('category_id')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            static::generateSku($get, $set);
                        })
                        ->relationship('category', 'name', fn($query) => $query->where('is_active', true))
                        ->createOptionForm([
                            FileUpload::make('image'),
                            TextInput::make('name'),
                            Toggle::make('is_active'),
                        ]),

                    Select::make('sub_category_id')
                        ->label('Sub Category')
                        ->options(function (Get $get) {
                            $CategoryId = $get('category_id');

                            if (!$CategoryId)
                                return [];
                            return SubCategory::where('category_id', $CategoryId)
                                ->pluck('name', 'id');
                        })
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            static::generateSku($get, $set);
                        })
                        ->reactive()
                        ->disabled(fn(callable $get) => $get('category_id') === null)
                        ->dehydrated()
                        ->createOptionForm([
                            FileUpload::make('image'),
                            Select::make('category_id')
                                ->options(Category::pluck('name', 'id')),
                            TextInput::make('name'),
                            Toggle::make('is_active'),
                        ])->createOptionUsing(function (array $data, Get $get): int {
                            $data['category_id'] = $data['category_id'] ?? $get('category_id');
                            return SubCategory::create($data)->getKey();
                        }),

                    FileUpload::make('images')
                        // ->directory('')
                        ->alignBetween()
                        ->columnSpanFull()
                        ->default(null),
                ])
                    ->description('Assosiation')
                    ->columnSpan(1)

            ])->columns(3);
    }
}
