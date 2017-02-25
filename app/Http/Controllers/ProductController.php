<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;


class ProductController extends Controller
{
    public function all(Request $request)
    {
        $products = Product::all();
        return response()->json($products,200);
    }

    public function get(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product,200);
    }

    public function add(Request $request)
    {
        $rules = [
            'name' => 'required|alpha|min:2',
            'price' => 'required|numeric|min:0' ,
            'in_stock' => 'sometimes|boolean'
        ];

        $this->validate($request, $rules);
        $product = Product::create($request->all());
        return response()->json($product,201);
    }

    public function patch(Request $request ,$id)
    {

        $rules = [
            'name' => 'sometimes|alpha|min:2',
            'price' => 'sometimes|numeric|min:0' ,
            'in_stock' => 'sometimes|boolean'
        ];

        $this->validate($request, $rules);
        $product = Product::findOrFail($id);
        $product->update($request->all());

        return response()->json([], 204);
    }

    public function delete(Request $request ,$id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([],204);
    }
}
