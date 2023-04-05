<?php

namespace App\Http\Controllers\back;

use App\Http\Controllers\Controller;
use App\Models\Cmf;
use App\Models\Step;
use App\Models\User;
use Illuminate\Http\Request;

class MrController extends Controller
{
    public function index()
    {
        $cmfs = Cmf::where([
            ['step','>=', 1],
            ['step', '<=',5],
        ])->orWhere('step',9)->get();
        return view('themes.back.mr.index',compact(
            'cmfs',
        ));
    }

    public function verifikasi($slug)
    {
        $cmf = Cmf::where([
            ['slug', $slug],
            ['step','>=', 1],
            ['step', '<=',5],
        ])->firstOrFail();
        $steps = Step::where('cmf_id', $cmf->id)->get();
        $depthead_pic = User::where('department_id', $cmf->user->department_id)->role('depthead pic')->first();

        $user_depthead = [];
        $cmf->departments->each(function ($item, $key) use (&$user_depthead) {
            $user_depthead[] = $item->pivot->department_id;
        });
        $depthead = User::role('depthead')->whereIn('department_id',$user_depthead)->get();
        $svp = User::role('svp system')->first();
        $mnf = User::role('mnf')->first();
        $mr = User::role('mr & food safety team')->first();
        return view('themes.back.mr.verifikasi',compact(
            'cmf',
            'steps',
            'depthead_pic',
            'depthead',
            'svp',
            'mnf',
            'mr',
        ));
    }

    public function store($slug, Request $request)
    {
        $cmf = Cmf::where([
            ['slug', $slug],
        ])->firstOrFail();
        $user = User::find(auth()->user()->id)->first();
        $is_signature = $request['verifikasi'] == "setuju";
        if($cmf->step == 1 && auth()->user()->hasRole('mr & food safety team')){
            $depthead_pic = User::where('name', $request['depthead_pic'])->first();
            $request->validate([
                'catatan' => 'required',
                'verifikasi' => 'required'
            ]);
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $depthead_pic->id,
                'step' => 2,
                'is_signature' => $is_signature,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui Depthead PIC' : 'Pengajuan Request Perubahan Tidak Disetujui Depthead PIC',
                'updated_by' => $user->name,
                'step' => 2
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 2 && auth()->user()->hasRole('mr & food safety team')){
            $depthead = User::whereIn('name', $request->depthead)->get();
            foreach ($depthead as $key => $item){
                Step::create([
                    'cmf_id' => $cmf->id,
                    'user_id' => $item->id,
                    'step' => 3,
                    'is_signature' => $request->verifikasi[$key] == "setuju",
                    'catatan' => $request->catatan[$key]
                ]);

                $cmf->update([
                    'status_pengajuan' => $request->verifikasi[$key] == "setuju" ? 'Pengajuan Request Perubahan Disetujui Depthead' : 'Pengajuan Request Perubahan Tidak Disetujui Depthead',
                    'updated_by' => $user->name,
                    'step' => 3
                ]);
            }
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 3 && auth()->user()->hasRole('mr & food safety team')){
            $svp = User::where('name', $request['svp'])->first();
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $svp->id,
                'step' => 4,
                'is_signature' => $is_signature,
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui SVP' : 'Pengajuan Request Perubahan Tidak Disetujui SVP',
                'updated_by' => $user->name,
                'step' => 4
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 4 && auth()->user()->hasRole('mr & food safety team')){
            $mnf = User::where('name', $request['mnf'])->first();
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => $mnf->id,
                'step' => 5,
                'is_signature' => $is_signature,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui MNF' : 'Pengajuan Request Perubahan Tidak Disetujui MNF',
                'updated_by' => $user->name,
                'step' => 5
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }elseif($cmf->step == 5 && auth()->user()->hasRole('mr & food safety team')){
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => auth()->user()->id,
                'step' => 6,
                'is_signature' => $is_signature,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui MR' : 'Pengajuan Request Perubahan Tidak Disetujui MR',
                'updated_by' => $user->name,
                'step' => 6
            ]);
            return redirect()->route('cmf.mr.index')
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }
        else{
            return back()->with('message-error','Anda sudah melakukan verifikasi');
        }
    }

    public function evaluasi($slug)
    {
        $cmf = Cmf::where([
            ['slug', $slug],
            ['step', '>=', 9]
        ])->firstOrFail();
        $steps = Step::where('cmf_id', $cmf->id)->get();
        return view('themes.back.mr.evaluasi',compact(
            'cmf',
            'steps'
        ));
    }

    public function update($slug, Request $request)
    {
        $cmf = Cmf::where([
            ['slug', $slug],
        ])->firstOrFail();
        $user = User::find(auth()->user()->id)->first();
        if($cmf->step == 9 && auth()->user()->hasRole('mr & food safety team')){
            $is_signature = $request['verifikasi'] == "setuju";
            Step::create([
                'cmf_id' => $cmf->id,
                'user_id' => auth()->user()->id,
                'step' => 10,
                'is_signature' => $is_signature,
                'catatan' => $request['catatan']
            ]);

            $cmf->update([
                'status_pengajuan' => $is_signature ? 'Pengajuan Request Perubahan Disetujui MR' : 'Pengajuan Request Perubahan Tidak Disetujui MR',
                'updated_by' => $user->name,
                'step' => 10
            ]);
            return back()
                ->with('message','Request Perubahan CMF Berhasil diverifikasi');
        }
        else{
            return back()->with('message-error','Anda sudah melakukan verifikasi');
        }
    }
}
