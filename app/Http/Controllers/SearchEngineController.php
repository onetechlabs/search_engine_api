<?php

namespace App\Http\Controllers;

use App\Searchenginelist;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DB;

class SearchEngineController extends Controller
{
    public function __construct(Request $request){
        $this->perpage = 10;
        $this->page = (null !== $request->input("page"))? (int)$request->input("page"):1;
        $this->spage = ($this->page > 1) ? ($this->page * $this->perpage) - $this->perpage : 0;
    }

    public function searchEngineList(Request $request) {
      $searchengine_lists = Searchenginelist::all()->orderBy('id', 'desc')->get();
      $total_searchengine_list = Searchenginelist::all()->count();
      $out = [
          "message" => "Success",
          "data"    => [
            "total_data"=>$total_searchengine_list,
            "records"=>$searchengine_lists
          ],
          "code"    => 200
      ];
      return response()->json($out, $out['code']);
    }

    public function searchEngineResults(Request $request) {
      $clientGuzzle = new \GuzzleHttp\Client();
      $sc_id = $request->input("sc_id");
      $keyword = $request->input("keyword");

      if($sc_id==1){
        $response = $clientGuzzle->get('https://www.googleapis.com/customsearch/v1?key=AIzaSyAEBgefbPwuDoFxAtizi2fT59qDhyDHLrw&cx=017062374917054110407:uvzcay15__s&q='.$keyword.'&num='.$this->perpage.'&start='.$this->spage);
        $out = json_decode($response->getBody());
        $sc_name=Searchenginelist::where("id",$sc_id)->get();

        $searchengine_results = $out->items;
        $total_searchengine_results = $out->queries->request[0]->totalResults;
        $total_page = ceil($total_searchengine_results / $this->perpage);
        $out = [
            "message" => "Success",
            "data"    => [
              "search_by"=>$sc_name[0]->sc_name,
              "total_page"=>$total_page,
              "total_data"=>$total_searchengine_results,
              "current_page" => $this->page,
              "records"=>$searchengine_results
            ],
            "code"    => 200
        ];
      } else if($sc_id==2){
        $response = $clientGuzzle->request('GET','https://api.cognitive.microsoft.com/bingcustomsearch/v7.0/search?customconfig=340d0b33-bc42-415d-8c70-cbb5e4b3ad52&mkt=en-US&q='.$keyword.'&count='.$this->perpage.'&offset='.$this->spage, [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => 'dcd7eff56dd44af9b58ff9509a70b06f',
            ]
        ]);
        $out = json_decode($response->getBody());

        $sc_name=Searchenginelist::where("id",$sc_id)->get();

        $searchengine_results = $out->webPages->value;
        $total_searchengine_results = $out->webPages->totalEstimatedMatches;
        $total_page = ceil($total_searchengine_results / $this->perpage);
        $out = [
            "message" => "Success",
            "data"    => [
              "search_by"=>$sc_name[0]->sc_name,
              "total_page"=>$total_page,
              "total_data"=>$total_searchengine_results,
              "current_page" => $this->page,
              "records"=>$searchengine_results
            ],
            "code"    => 200
        ];
      } else {
        $out = [
            "message" => "Please, Fill Search Engine ID!",
            "code"   => 500,
        ];
      }

      return response()->json($out, $out['code']);
    }

    /*STANDARD CRUD*/

    // Search Engine List
    public function searchengine_lists(Request $request){
        $searchengine_lists = Searchenginelist::limit($this->perpage)->offset($this->spage)->orderBy('id', 'desc')->get();
        $total_searchengine_list = Searchenginelist::all()->count();
        $total_page = ceil($total_searchengine_list / $this->perpage);
        $out = [
            "message" => "Success",
            "data"    => [
              "total_page"=>$total_page,
              "total_data"=>$total_searchengine_list,
              "current_page" => $this->page,
              "records"=>$searchengine_lists
            ],
            "code"    => 200
        ];
        return response()->json($out, $out['code']);
    }

    public function searchengine_listShow(Request $request, $id){
        $total_searchengine_list = Searchenginelist::where("id",$id)->count();
        if($total_searchengine_list !==0){
          $searchengine_list = Searchenginelist::where("id",$id)->get();
          $out = [
              "message" => "Success",
              "data"    => [
                "record"=> $searchengine_list
              ],
              "code"    => 200
          ];
        }else{
          $out = [
              "message" => "Failed to Search Engine List",
              "code"   => 500,
          ];
        }
        return response()->json($out, $out['code']);
    }

    public function searchengine_listCreate(Request $request){
        $validate=\Validator::make($request->all(),
        array(
          'sc_name' => 'required|min:5'
        ));

        if ($validate->fails()) {
            $out = [
                "message" => $validate->messages(),
                "code"    => 500
            ];
            return response()->json($out, $out['code']);
        }

        $sc_name = $request->input("sc_name");

        $data = [
            "sc_name" => $sc_name
        ];

        if (Searchenginelist::create($data)) {
            $out = [
                "message" => "Success",
                "code"    => 200,
            ];
        } else {
            $out = [
                "message" => "Failed to Search Engine List",
                "code"   => 500,
            ];
        }

        return response()->json($out, $out['code']);
    }

    public function searchengine_listUpdate(Request $request, $id){
        $total_searchengine_list = Searchenginelist::where("id",$id)->count();
        if($total_searchengine_list !==0){
          $validate=\Validator::make($request->all(),
          array(
            'sc_name' => 'required|min:5'
          ));

          if ($validate->fails()) {
              $out = [
                  "message" => $validate->messages(),
                  "code"    => 500
              ];
              return response()->json($out, $out['code']);
          }

          $sc_name = $request->input("sc_name");

          $searchengine_list = Searchenginelist::find($id);
          $searchengine_list->sc_name = $sc_name;
          $searchengine_list->save();

          $out = [
              "message" => "Success",
              "code"    => 200,
          ];
        }else{
          $out = [
              "message" => "Failed to Search Engine List",
              "code"   => 500,
          ];
        }
        return response()->json($out, $out['code']);
    }

    public function searchengine_listDelete(Request $request, $id){
        $total_searchengine_list = Searchenginelist::where("id",$id)->count();
        if($total_searchengine_list !==0){
          $searchengine_list = Searchenginelist::find($id);
          $searchengine_list->delete();
          $out = [
              "message" => "Success",
              "code"    => 200,
          ];
        }else{
          $out = [
              "message" => "Failed to Search Engine List",
              "code"   => 500,
          ];
        }
        return response()->json($out, $out['code']);
    }

}
