<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Mail\ContactEnquiryMail;
use App\Models\ContactEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index(){
        return view('frontend.index');
    }

       public function landing(){
        return view('frontend.landing');
    }
    public function aboutUs(){
        return view('frontend.about');
    }

    public function contact(){
        return view('frontend.contact');
    }
    public function szorzoAi(){
        return view('frontend.szorzo-ai');
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
    public function enterpriseLearningSolution(){
        return view('frontend.services.enterprise-learning-solution');
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

    public function szorzoAiForm(){
        return view('frontend.contact-forms.szorzo-ai-form');
    }
    public function enterpriseTransformationForm(){
        return view('frontend.contact-forms.enterprise-transformation-form');
    }
    public function enterpriseDigitalizationForm(){
        return view('frontend.contact-forms.enterprise-digitalization-form');
    }
    public function enterpriseLearningSolutionForm(){
        return view('frontend.contact-forms.enterprise-learning-solution-form');
    }
    public function organizationCapacityForm(){
        return view('frontend.contact-forms.organization-capacity-form');
    }
    public function operationHrOfferingForm(){
        return view('frontend.contact-forms.operation-hr-offering-form');
    }
    public function itServicesForm(){
        return view('frontend.contact-forms.it-services-form');
    }
    public function mergerServicesForm(){
        return view('frontend.contact-forms.merger-service-form');
    }

    public function contactStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname'    => 'required|string|max:100',
            'lastname'     => 'required|string|max:100',
            'email'        => 'required|email',
            'company'      => 'required|string|max:150',
            'relationship' => 'required|string',
            'phone'        => 'required|regex:/^[0-9]{10}$/',
            'info'         => 'nullable|string',
        ], [
            'phone.regex' => 'Phone must be exactly 10 digits.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $request->only(
            'firstname','lastname','email','company','relationship','phone','info'
        );

        ContactEnquiry::create($data);

        // Send Email
        Mail::to('elangokrish310@gmail.com')->send(new ContactEnquiryMail($data));

        return response()->json([
            'success' => true,
            'message' => 'Your message has been submitted successfully!',
        ]);
    }

    public function telecomServices(){
        return view('frontend.telecom-services');
    }
    public function feedbackForm(){
        return view('frontend.feedback-form');
    }
}
