<?php

namespace App\Http\Controllers;

use App\Contracts\AIProviderInterface;
use Illuminate\Http\Request;
use App\Services\PortalKnowledgeProvider;

class AIChatController extends Controller
{
    public function __invoke(Request $request, AIProviderInterface $ai)
    {
        $data = $request->validate(['message' => 'required|string|max:500', 'history'=>'nullable|array|max:12', 'history.*.role'=>'required_with:history|in:user,assistant', 'history.*.content'=>'required_with:history|string|max:500']);

        return response()->json($ai instanceof PortalKnowledgeProvider ? $ai->respond($data['message'],$data['history']??[]) : ['answer'=>$ai->chat($data['message']),'links'=>[],'suggestions'=>[]]);
    }
}
