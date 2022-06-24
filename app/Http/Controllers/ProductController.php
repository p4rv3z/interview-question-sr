<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $products = $products->paginate(2);
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
            if (request()->hasfile('product_image')) {
                foreach ($request->file('product_image') as $image) {
                    $imageName = "image_" . rand(0, 100) . time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('images/products'), $imageName);
                    ProductImage::create([
                        'file_path' => public_path('images/products/' . $imageName),
                        'thumbnail' => true,
                        'product_id' => $product->id,
                    ]);
                }
            }
            $productVariantPrices = request()->product_variant_prices;
            $productVariants = request()->product_variant;
            $variantOne = null;
            $variantTwo = null;
            $variantThree = null;
            foreach ($productVariants as $k => $productVariant) {
                //first variant like color
                if ($k == 0) $variantOne = $productVariant;
                if ($k == 1) $variantTwo = $productVariant;
                if ($k == 2) $variantThree = $productVariant;
            }

            if ($variantOne != null) {

                $variantOneId = $variantOne['option'];
                foreach ($variantOne['tags'] as $tag) {
                    $pVariantOne = ProductVariant::create([
                        'variant_id' => $variantOneId,
                        'product_id' => $product->id,
                        'variant' => $tag,
                    ]);

                    if ($variantTwo != null) {
                        $variantTwoId = $variantTwo['option'];
                        foreach ($variantTwo['tags'] as $tag) {
                            $pVariantTwo = ProductVariant::create([
                                'variant_id' => $variantTwoId,
                                'product_id' => $product->id,
                                'variant' => $tag,
                            ]);

                            if (count($productVariants) == 2) {
                                $productVariantPriceTwo = new ProductVariantPrice;
                                $productVariantPriceTwo->product_variant_one = $pVariantOne->id;
                                $productVariantPriceTwo->product_variant_two = $pVariantTwo->id;
                                $productVariantPriceTwo->product_id = $product->id;
                                $v = $this->findPrice($productVariantPrices,
                                    $this->generateTitle($pVariantOne->variant, $pVariantTwo->variant, null));
                                $productVariantPriceTwo->price = $v['price'];
                                $productVariantPriceTwo->stock = $v['stock'];
                                $productVariantPriceTwo->save();
                            }

                            if ($variantThree != null) {
                                $variantThreeId = $variantThree['option'];
                                foreach ($variantThree['tags'] as $tag) {
                                    $pVariantThree = ProductVariant::create([
                                        'variant_id' => $variantThreeId,
                                        'product_id' => $product->id,
                                        'variant' => $tag,
                                    ]);
                                    if (count($productVariants) == 3) {
                                        $productVariantPriceThree = new ProductVariantPrice;
                                        $productVariantPriceThree->product_variant_one = $pVariantOne->id;
                                        $productVariantPriceThree->product_variant_two = $pVariantTwo->id;
                                        $productVariantPriceThree->product_variant_three = $pVariantThree->id;
                                        $productVariantPriceThree->product_id = $product->id;
                                        $v = $this->findPrice($productVariantPrices,
                                            $this->generateTitle($pVariantOne->variant, $pVariantTwo->variant, $pVariantThree->variant));
                                        $productVariantPriceThree->price = $v['price'];
                                        $productVariantPriceThree->stock = $v['stock'];
                                        $productVariantPriceThree->save();
                                    }
                                }
                            }
                        }
                    }

                    if (count($productVariants) == 1) {
                        $productVariantPriceOne = new ProductVariantPrice;
                        $productVariantPriceOne->product_variant_one = $pVariantOne->id;
                        $productVariantPriceOne->product_id = $product->id;
                        $v = $this->findPrice($productVariantPrices,
                            $this->generateTitle($pVariantOne->variant, null, null));
                        $productVariantPriceOne->price = $v['price'];
                        $productVariantPriceOne->stock = $v['stock'];
                        $productVariantPriceOne->save();
                    }
                }
            } else {
                return false;
            }
            return true;
        });
        return $result;
    }

    public function findPrice($productVariantPrices, $title)
    {
        foreach ($productVariantPrices as $v) {
            if ($v['title'] == $title) return $v;
        }
        return null;
    }

    public function generateTitle($one = null, $two = null, $three = null)
    {
        $title = "";
        if ($one != null) {
            $title .= $one . "/";
        }
        if ($two != null) {
            $title .= $two . "/";
        }
        if ($three != null) {
            $title .= $three . "/";
        }
        return $title;
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public
    function show($product)
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
        $product_prices = $this->productPrices($product->id);
        $product = Product::with(['productVarients', 'productVarientPrice', 'images'])->where('id', $product->id)->first();
        $productVariants = $this->productVariants($product->id);
        return view('products.edit', compact(
            'variants', 'product', 'product_prices','productVariants'
        ));
    }

    public function productVariants($id)
    {
        $productVariants = ProductVariant::where('product_id', $id)->get()->groupBy('variant_id');

        $result = array();
        foreach ($productVariants as $k => $pvs) {
            $tmp = array();
            $tmp['option'] = $k;
            $tags = array();
            foreach ($pvs as $pv) {
                array_push($tags, $pv->variant);
            }
            $tmp['tags'] = $tags;
            array_push($result, $tmp);
        }
        return json_encode($result);
    }

    public function productPrices($id)
    {
        $result = array();
        $findProductPrices = ProductVariantPrice::with(['productVariantOne', 'productVariantTwo',
            'productVariantThree'])->where('product_id', $id)->get();
        foreach ($findProductPrices as $pp) {

            $title = $this->generateTitle(
                $pp->productVariantOne != null ? $pp->productVariantOne->variant : null
                , $pp->productVariantTwo != null ? $pp->productVariantTwo->variant : null
                , $pp->productVariantThree != null ? $pp->productVariantThree->variant : null
            );
            $tmp = array('title' => $title, 'price' => ($pp->price) . "", 'stock' => ($pp->stock) . "");
            array_push($result, $tmp);
        }
        return json_encode($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public
    function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public
    function destroy(Product $product)
    {
        //
    }
}
