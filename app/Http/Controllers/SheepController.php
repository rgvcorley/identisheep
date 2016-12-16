<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Homebred;
use App\Models\Sheep;
use App\user;
use Auth,View,Input,Redirect,Validator,Session,Carbon\Carbon,DB;
use Illuminate\Pagination\Paginator;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class SheepController extends Controller {
    /**
     * SheepController constructor.
     *
     * cause to be Auth filtered before use
     */
    public function __construct(){
        $this->middleware('auth');
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$ewes = Sheep::females($this->user());

        return view('sheeplist')->with([
            'ewes'=>$ewes[0],
            'title'=>'All Female Sheep',
            'count'=>$ewes[1]
            ]);
	}
    public static function user()
    {
        return Auth::user()->id;
    }
    public function getEwes()
    {
        $ewes = Sheep::stock($this->user(),'female');

        return view('sheeplist')->with([
            'ewes'=>$ewes[0],
            'title'=>'All Female Sheep',
            'count'=>$ewes[1]
        ]);
    }
    public function getTups()
	{
        $ewes = Sheep::stock($this->user(),'male');

        return view('sheeplist')->with([
            'ewes'=>$ewes[0],
            'title'=>'All Tups ',
            'count'=>$ewes[1]
        ]);
	}
    public function getOfflist()
    {
        $ewes = Sheep::offList($this->user());

        return view('sheeplist')->with([
            'ewes'=>$ewes,
            'title'=>'Sheep Moved Off'
        ]);
    }
    public function getDeadlist()
    {
        $ewes = Sheep::dead($this->user());

        return view('sheeplist')->with([
            'ewes'=>$ewes,
            'title'=>'Dead List'
        ]);
    }
	/**
	 * Show the form for deleting records.
	 *
	 * @return Response
	 */
	public function getDelete()
	{
        $year = date('Y', strtotime('-10 years'));
        return View::make('delete_old')->with([
            'title'         => 'Delete old Records',
            'year'          => $year

        ]);
	}
	public function postDelete()
    {
        $rules = Sheep::$rules['old_dates'];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation->messages());
        }
        $year = Input::get('year');
        $date = Carbon::create($year,12,31,0);
        $date = $date->toDateTimeString();
        Sheep::withTrashed()->where('move_on','<=',$date)->forceDelete();

        return Redirect::to('sheep/list');

    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  none
	 * @return Response
	 */
	public function postChangetags()
	{
        $id = Input::get('id');
        $old_e_flock = Input::get('old_e_flock');
		$ewe = Sheep::where('id',$id)->first();
        if ($ewe->e_tag != Input::get('e_tag')) {
            $ewe->e_tag_2 = $ewe->e_tag_1;
            $ewe->e_tag_1 = $ewe->e_tag;
            $ewe->e_tag = Input::get('e_tag');
        }
        $ewe->e_flock = Input::get('e_flock');
        $ewe->save();
        return View::make('sheepfinder')->with([
            'title'     => 'Find another sheep',
            'e_flock'   => $old_e_flock]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        //
	}
    /**
     * Update Tag numbers
     *
     * @param int $id
     *
     * @return view
     */
    public function getEdit($id)
    {
        $ewe = Sheep::getById($id);
        //return $ewe->id;
        return View::make('sheepedit')->with([
            'id'        =>$id,
            'title'     => 'Change Tags',
            'e_flock'   =>$ewe->e_flock,
            'e_tag'     =>$ewe->e_tag
        ]);
    }
    /**
     * Update Tag numbers
     *
     * @param none
     *
     * @return view
     */
    public function getEditmore($flock_old,$flock_new)
    {
        //$ewe = Sheep::getById($id);
        //return $ewe->id;
        return View::make('sheepeditmore')->with([
            'id'        =>$id,
            'title'     => 'Change more Tags',
            'e_flock'   =>$ewe->e_flock,
            'e_tag'     =>$ewe->e_tag
        ]);
    }
    /**
     * Update finding by tag number
     *
     * @param int $tag
     *
     * @param string $flock_number
     *
     * @return view
     */
    public function postSeek()
    {

        $e_flock = Input::get('flock');
        //dd($e_flock);
        $e_tag    =Input::get('tag');
        $ewe = Sheep::getByTag($e_flock, $e_tag);
            if ($ewe == NULL){Session::put('find_error','Sheep not found, check numbers and re-try.');
                return Redirect::to('sheep/seek')->withInput();
            }
        if (Input::get('find')){
            return View::make('sheepedit')->with([
                'id'            =>$ewe->id,
                'title'         => 'Change Tags',
                'e_flock'       =>$e_flock,
                'e_tag'         =>$ewe->e_tag,
                'original_e_flock'=>$ewe->original_e_flock,
                'colour_flock'  => $ewe->colour_flock,
            ]);
        }
        if (Input::get('view')){
            return View::make('sheepview')->with([
                'id'            =>$ewe->id,
                'title'         =>'Details for Sheep number ',
                'e_flock'       =>$e_flock,
                'e_tag'         =>$ewe->e_tag,
                'e_tag_1'       =>$ewe->e_tag_1,
                'e_tag_2'       =>$ewe->e_tag_2,
                'original_e_flock'=>$ewe->original_e_flock,
                'original_e_tag'=>$ewe->original_e_tag,
                'colour_of_tag' =>$ewe->colour_of_tag,
                'move_on'       =>$ewe->move_on,
                'sex'           =>$ewe->sex
            ]);
        }
    }
    /**
     * Find sheep page
     *
     * @param none
     * @return view
     */
    public function getSeek()
    {
        return View::make('sheepfinder')->with([
            'title'     => 'Find a sheep'
        ]);
    }
    /**
     * Load Batch entry form
     *
     * @param none
     *
     * @return view
     */
    public function getBatch()
    {
        return View::make('sheepbatch')->with([
            'id'=>$this->user(),
            'title' => 'Enter Batch of tags'
        ]);
    }
    public function getAddewe($home_bred)
    {
        return View::make('sheepaddewe')
            ->with([
                'title'     => 'Add a Ewe',
                'alt_title' => 'Add a Home Bred Ewe',
                'id'        =>  $this->user(),
                'sex'       => 'female',
                'home_bred'  => $home_bred
            ]);
    }
    public function getAddtup($home_bred)
    {
        return View::make('sheepaddewe')
            ->with([
                'title'     => 'Add a Tup',
                'alt_title'     => 'Add a Home Bred Tup',
                'id'        =>  $this->user(),
                'sex'       => 'male',
                'home_bred'  => $home_bred
            ]);
    }
    public function postAddewe()
    {
        $rules = Sheep::$rules['dates_and_tags'];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation->messages());
        }
        $user_id        = $this->user();
        $home_bred      = Input::get('home_bred');
        $e_flock        = Input::get('e_flock');
        $e_flock_number = $e_flock;
        $e_tag          = Input::get('e_tag');
        $d              = Input::get('day');
        $m              = Input::get('month');
        $y              = Input::get('year');
        $colour_of_tag  = Input::get('colour_of_tag');
        $move_on        = $y.'-'.$m.'-'.$d.' '.'00:00:00';
        $sex            = Input::get('sex');
        $l              = DB::table('sheep')->where('user_id',$user_id)->max('local_id');
        $sheep_exists   = Sheep::check($e_flock_number,$e_tag,$user_id);
        if (NULL === $sheep_exists) {
            $l++;
            $ewe = new Sheep();
            $ewe->setUserId($user_id);
            $ewe->setLocalId($l);
            $ewe->setElectronicFlockNumber($e_flock_number);
            $ewe->setOriginalElectronicFlockNumber($e_flock_number);
            $ewe->setColourTagFlockNumber($e_flock_number);
            $ewe->setTagNumber($e_tag);
            $ewe->setOriginalTagNumber($e_tag);
            $ewe->setColourTagNumber($e_tag);
            $ewe->setMoveOn($move_on);
            $ewe->setColourOfTag($colour_of_tag);
            $ewe->setSex($sex);
            $ewe->save();

            if($home_bred !== FALSE){
                $tag = new Homebred();
                $tag->setFlockNumber($home_bred);
                $tag->setDateApplied($move_on);
                $tag->setUserId($user_id);
                $tag->setCount(1);
                $tag->save();
            }
        }

        return Redirect::back()->withInput([Input::except('e_tag')]);/*[
            'e_flock'   =>$e_flock,
            'sex'       =>$ewe->sex,
            'day'       =>$d,
            'month'     =>$m,
            'year'      =>$y,
            'colour_of_tag'=>$colour_of_tag

        ]);*/
    }
    public function getSheepoff($sex)
    {
        return View::make('sheepoff')->with([
            'title'     => 'Single '.ucwords($sex).' Sheep Off',
            'id'        => $this->user(),
            'sex'       => $sex,
            'e_flock'   => NULL,
            'e_tag'     => NULL
        ]);
    }
    public function postSheepoff()
    {
        $rules = Sheep::$rules['dates_and_tags'];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation->messages());
        }
        $e_flock        = Input::get('e_flock');
        $e_flock_number = $e_flock;
        $e_tag          = Input::get('e_tag');
        $d              = Input::get('day');
        $m              = Input::get('month');
        $y              = Input::get('year');
        $move_off       = $y.'-'.$m.'-'.$d.' '.'00:00:00';
        $destination    = Input::get('destination');
        $sex            = Input::get('sex');

        $ewe = Sheep::firstOrNew([
            'e_flock'           =>  $e_flock_number,
            'e_tag'             =>  $e_tag,
            'user_id'           =>  $this->user()
        ]);
        $ewe->setOriginalElectronicFlockNumber($e_flock_number);
        $ewe->setColourTagFlockNumber($e_flock_number);
        $ewe->setTagNumber($e_tag);
        $ewe->setMoveOff($move_off);
        $ewe->setOffHow($destination);
        $ewe->setSex($sex);
        $ewe->save();
        $ewe->delete();

        return Redirect::back()->with([
            'title'     => 'Single '.ucwords($ewe->getSex()).' Sheep Off',
            'id'        => $this->user(),
            'e_flock'   => NULL,
            'e_tag'     => NULL,
            'sex'       => $ewe->getSex()
        ]);

    }

    public function getDeath()
    {
        return View::make('sheepdeath')->with([
            'title'     => 'Record a Death',
            'id'        => $this->user(),
            'sex'       =>'female',
            'e_flock'   => NULL,
            'e_tag'     => NULL
        ]);
    }
    public function postDeath()
    {
        $rules = Sheep::$rules['dates_and_tags'];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation->messages());
        }
        $e_flock        = Input::get('e_flock');
        $e_flock_number = $e_flock;
        $e_tag          = Input::get('e_tag');
        $d              = Input::get('day');
        $m              = Input::get('month');
        $y              = Input::get('year');
        $move_off       = $y.'-'.$m.'-'.$d.' '.'00:00:00';
        $how_died       = ' - '.Input::get('how_died');
        $sex            = Input::get('sex');

        $ewe = Sheep::firstOrNew([
            'e_flock'           =>  $e_flock_number,
            'e_tag'             =>  $e_tag,
            'user_id'           =>  $this->user()
        ]);
        $ewe->setOriginalElectronicFlockNumber($e_flock_number);
        $ewe->setColourTagFlockNumber($e_flock_number);
        $ewe->setTagNumber($e_tag);
        $ewe->setMoveOff($move_off);
        $ewe->setOffHow('died'.$how_died);
        $ewe->setSex($sex);
        $ewe->save();
        $ewe->delete();

        return View::make('sheepdeath')->with([
            'title'     => 'Record a Death',
            'id'        => $this->user(),
            'e_flock'   => NULL,
            'e_tag'     => NULL,
            'sex'       => $ewe->getSex()
        ]);
    }
    public function getEnterdeath($id,$e_flock,$e_tag,$sex)
    {
        return View::make('sheepdeath')->with([
            'title'     => 'Record a Death',
            'id'        => $id,
            'e_flock'   => $e_flock,
            'e_tag'     => $e_tag,
            'sex'       => $sex
        ]);
    }
    public function getSearch()
    {
         return View::make('search')->with([
             'title'=>'Search for a Tag'
         ]);
    }
    public function getReplaced()
    {
        $ewes = Sheep::replaced($this->user());

        return View::make('replacement_tags')->with([
            'ewes'=>$ewes[0],
            'count'=>$ewes[1],
            'title'=>'Tag Replacements List'
        ]);
    }
    public function postSearch()
    {
        $tag = Input::get('tag');

        $ewes = Sheep::searchByTag($this->user(),$tag);
        return View::make('searchresult')->with([
            'ewes'=>$ewes,
            'title'=>'Search Results for Tag '.$tag
        ]);
    }
    public function getNoneid()
    {
        $ewes = Sheep::total($this->user());

        return view('sheeplist')->with([
            'ewes'=>$ewes[0],
            'title'=>'All Tagged Sheep',
            'count'=>$ewes[1]
        ]);
    }
}
