<?php
  
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use Session;
use App\Models\UserCode;
  
class TwoFAController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        return view('2fa');
    }

    // Generar link firmado
    public function getLinkSubscribe()
    {
        return URL::temporarySignedRoute(
            'store', 
            now()->addMinutes(5), 
            ['event' => Event::first(), 'user' => auth()->user()]
        );
    }
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function store(Request $request)
    {
        if (! $request->hasValidSignature()) {
            //return abort(401);
            return back()->with('error', 'Código Expiro genera un codigo nuevo.');
        }
        $request->validate([
            'code'=>'required',
        ]);
  
        $find = UserCode::where('user_id', auth()->user()->id)
                        ->where('code', $request->code)
                        ->where('updated_at', '>=', now()->subMinutes(2))
                        ->first();
  
        if (!is_null($find)) {
            Session::put('user_2fa', auth()->user()->id);
            return redirect()->route('home');
        }
  
        return back()->with('error', 'Código Incorrecto.');
    }
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function resend()
    {
        auth()->user()->generateCode();
  
        return back()->with('success', 'Te enviaremos un codigo por email.');
    }
}