<?php

namespace App\Http\Controllers;

use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;

class WhatsappTemplateController extends Controller
{
    public function index()
    {
        $templates = WhatsappTemplate::all();

        return view('whatsapp_templates.index', compact('templates'));
    }

    public function update(Request $request, WhatsappTemplate $whatsappTemplate)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $whatsappTemplate->update([
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'WhatsApp template updated successfully.');
    }
}
