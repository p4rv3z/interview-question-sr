@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{route('product.index')}}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        @foreach($variants as $variant)
                            <optgroup label="{{$variant->title}}">
                                @foreach($variant->productVariantGroup() as $k=>$v)
                                    <option value="{{$k}}">{{$k}}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                               class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $loop->index+1 }}</td>
                            <td>{{$product->title}} <br> Created at : {{$product->createdAt()}}</td>
                            <td>{{$product->description}}</td>
                            <td>
                                <dl class="row mb-0" style="height: 80px; overflow: hidden" id="variant-{{$product->id}}">
                                    @foreach($product->productVarientPrice as $pVarient)
                                        <dt class="col-sm-3 pb-0">
                                            {{$pVarient->productVeriant()}}
                                        </dt>
                                        <dd class="col-sm-9">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4 pb-0">Price
                                                    : {{ number_format($pVarient->price,2) }}</dt>
                                                <dd class="col-sm-8 pb-0">InStock
                                                    : {{ number_format($pVarient->stock,2) }}</dd>
                                            </dl>
                                        </dd>
                                    @endforeach
                                </dl>
                                <button onclick="$('#variant-{{$product->id}}').toggleClass('h-auto')" class="btn btn-sm btn-link">Show
                                    more
                                </button>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center" colspan="5">No data found</td>
                        </tr>
                    @endforelse
                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} out
                        of {{$products->total()}}</p>
                </div>
                <div class="col-md-2">
                    <div>
                        {{$products->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
