<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $products = Product::with(['productVarientPrice', 'productVarients']);
        if (request()->has('title') && request()->get('title') != null) {
            $title = request()->get('title');
            $products->where('title', 'LIKE', "%" . $title . "%");
        }
        if (request()->has('variant') && request()->get('variant') != null) {
            $variant = request()->get('variant');
            $products->whereHas('productVarients', function ($query) use ($variant) {
                $query->where(function ($q) use ($variant) {
                    $q->where('variant', $variant);
                });
            });
        }
        if (request()->has('price_from') && request()->get('price_from') != null) {
            $fromPrice = request()->get('price_from');
            $products->whereHas('productVarientPrice', function ($query) use ($fromPrice) {
                $query->where(function ($q) use ($fromPrice) {
                    $q->where('price', ">=", $fromPrice);
                });
            });
        }
        if (request()->has('price_to') && request()->get('price_to') != null) {
            $toPrice = request()->get('price_to');
            $products->whereHas('productVarientPrice', function ($query) use ($toPrice) {
                $query->where(function ($q) use ($toPrice) {
                    $q->where('price', "<=", $toPrice);
                });
            });
        }
        if (request()->has('date') && request()->get('date') != null) {
            $fDate = date(request()->get('date'));
            $products->whereDate('created_at', $fDate);
        }

        $products = $products->paginate(200);
        $variants = Variant::all();
        return view('products.index', [
            'products' => $products,
            'variants' => $variants,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $result = DB::transaction(function () use ($request) {
//            Step One:: Add product
            $product = Product::create([
                'title' => $request->title,
                'sku' => $request->sku,
                'description' => $request->description,
            ]);

            //Step Two:: Add Product Image
            if (isset(request()->product_image)) {
                if (isset(request()->product_image)) {
                    $imageName = time() . '.' . request()->product_image->getClientOriginalExtension();
                    request()->product_image->move(public_path('images/products'), $imageName);
                    ProductImage::create([
                        'file_path' => public_path('images/products/' . $imageName),
                        'thumbnail' => true,
                        'product_id' => $product->id,
                    ]);
                }
            }
            $productVariantPrices = request()->product_variant_prices;
            $productVariants = request()->product_variant;
            foreach ($productVariants as $productVariant) {
                foreach ($productVariant->tags as $tag) {
                    $pVariant = ProductVariant::create([
                        'variant_id' => $productVariant->option,
                        'product_id' => $product->id,
                        'variant' => $tag,
                    ]);
                }

            }

            //Step Three:: Create Product Variant
        });
        return $result;
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
