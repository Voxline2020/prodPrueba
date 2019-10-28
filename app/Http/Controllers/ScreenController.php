<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateScreenRequest;
use App\Http\Requests\UpdateScreenRequest;
use App\Repositories\ScreenRepository;
use App\Repositories\VersionPlaylistDetailRepository;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Store;
use App\Models\Screen;
use App\Models\Computer;
use App\Models\Content;
use App\Models\VersionPlaylistDetail;
use App\Models\ScreenPlaylistAsignation;
use Flash;
use Response;

class ScreenController extends AppBaseController
{
	/** @var  ScreenRepository */
	private $screenRepository;
	private $versionPlaylistDetailRepository;
	public function __construct2(VersionPlaylistDetailRepository $screenRepo2)
	{
		$this->versionPlaylistDetailRepository = $screenRepo2;
	}

	public function __construct(ScreenRepository $screenRepo)
	{
		$this->screenRepository = $screenRepo;
	}
	//mostrar pantallas
	public function index(Request $request)
	{
		$screens = $this->screenRepository->all();
		return view('screen.index')
			->with('screens', $screens);
	}
	//mostrar pantallas con id en especifico
	public function show($id)
	{
		$computers = Computer::where('id', $id)->first();
		$screen = Screen::where('computer_id', $id)->paginate();
		return view(
			'screen.index',
			['screens' => $screen],
			compact('computers')
		);
	}
	public function AssignContent($id)
	{
		$content = Content::where('id', $id)->first();
		$screen = $this->screenRepository->all();
		return view(
			'screen.index_content_screen',
			['screens' => $screen],
			compact('content')
		);
	}

	public function ScreenPlaylistAsign(Request $request, $id)
	{
		$content = Content::where('id', $id)->first();
		$screen = $this->screenRepository->all();
		if ($request->pantallas != null) {
			foreach ($request->pantallas as $idScreen) {
				$playlist_asign = ScreenPlaylistAsignation::where('screen_id', $idScreen)->get();
				if ($playlist_asign != null) {
					$version_playlist_detail = new VersionPlaylistDetail;
					$playlist_asign = ScreenPlaylistAsignation::where('screen_id', $idScreen)->get();
					foreach ($playlist_asign as $playlist_asigns) {
						$version_playlist_detail->version_id = $playlist_asigns->version_id;
					}
					$version_playlist_detail->content_id = $id;
					$version_playlist_detail->save();
				}
			}
			Flash::success('pantalla asignada con contenido');
			return redirect(url()->previous());
		}
		else {
			Flash::error('no ha seleccionado ninguna pantalla');
			return redirect(url()->previous());
		}
	}
	//creacion screens
	//vista de creacion
	public function create($id)
	{
		$computers = Computer::where('id', $id)->get();
		return view('screen.create', compact('computers'));
	}
	//Request de creacion (POST)
	public function store(CreateScreenRequest $request)
	{
		$input = $request->all();
		$screen = $this->screenRepository->create($input);
		Flash::success('Pantalla agregada con exito.');
		return redirect(url()->previous());
	}
	//editar screens
	//vista de edicion.
	public function edit($id, $computer_id)
	{
		$computers = Computer::where('id', $computer_id)->get();
		$screen = $this->screenRepository->find($id);

		if (empty($screen)) {
			Flash::error('Tienda no encontrada');
			return redirect(route('screens.index'));
		}
		return view('screen.edit', compact('computers'))->with('screen', $screen);
	}
	//Request de edicion (POST)
	public function update($id, UpdateScreenRequest $request)
	{
		$screen = $this->screenRepository->find($id);
		if (empty($screen)) {
			Flash::error('Pantalla no encontrada');
			return redirect(url()->previous());
		}
		$screen = $this->screenRepository->update($request->all(), $id);
		Flash::success('Pantalla editada');
		return redirect(url()->previous());
	}
	//Eliminar screens
	public function destroy($id)
	{
		$screen = $this->screenRepository->find($id);
		if (empty($screen)) {
			Flash::error('pantalla no encontrada');
			return redirect(url()->previous());
		}
		$this->screenRepository->delete($id);
		Flash::success('Pantalla borrada');
		return redirect(url()->previous());
	}
//filtros.
	public function filter_by_name(Request $request,$id)
	{
		$content = Content::where('id', $id)->first();
		$filter= $request->get('nameFiltrar');
		$screen = Screen::where('name','LIKE',"%$filter%")->paginate();
		return view(
			'screen.index_content_screen',
			['screens' => $screen],
			compact('content')
		);
	}

}