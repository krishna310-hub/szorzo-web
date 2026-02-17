<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(){
        return view('frontend.index');
    }

    public function aboutUs(){
        return view('frontend.about');
    }

    public function contact(){
        return view('frontend.contact');
    }

    public function careers(){
        return view('frontend.careers');
    }
    public function careersList(){
        return view('frontend.careers-list');
    }
    public function enterpriceFormation(){
        return view('frontend.services.enterprice');
    }
    public function marketingService(){
        return view('frontend.services.marketing');
    }
    public function organizationCapacityAssessment(){
        return view('frontend.services.organization');
    }
    public function operationsInfrastructureOfferings(){
        return view('frontend.services.operations');
    }

    public function strategicAdvisory(){
        return view('frontend.services.merger.strategic');
    }
    public function targetIdentificationEvaluation(){
        return view('frontend.services.marketing');
    }
    public function dueDiligence(){
        return view('frontend.services.organization');
    }
    public function valuationDealStructuring(){
        return view('frontend.services.operations');
    }
    public function negotiationDealExecution(){
        return view('frontend.services.organization');
    }
    public function postMergerIntegration(){
        return view('frontend.services.operations');
    }

    public function dataCenterDesign(){
        return view('frontend.services.it.data-center-design');
    }
    public function dataCenterManagedService(){
        return view('frontend.services.it.data-center-managed-service');
    }
    public function itInfrastructure(){
        return view('frontend.services.it.it-infrastructure');
    }
    public function cyberSecurity(){
        return view('frontend.services.it.cyber-security');
    }
    public function certificateCompliance(){
        return view('frontend.services.it.certificate-compliance');
    }
    public function hardwareSoftware(){
        return view('frontend.services.it.hardware-software');
    }
}
