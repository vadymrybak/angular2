import { Component, OnInit } from '@angular/core';
import {AppDataService} from "./app-data.service";
import { Observable, Subscription } from 'rxjs';
import {NgForm} from '@angular/forms';
import { Validators, FormBuilder, Form, FormGroup } from '@angular/forms';
import SurveyDetails from './models/survey-details';
import BasicErrors from './models/basic-errors';
import UpdateXML from './models/update-xml';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  activeStage = 1;                    // Which view to show out of 3
  loading: boolean = true;           // If need to show loading animation
  projectForm: FormGroup;             // FormGroup for initial form (project number, json path)
  havePermission: boolean;            // Determines if user has permissions to use the app

  basicSetup: Subscription;
  projectDetails: Subscription;
  completeView: Subscription;
  permissions: Subscription;

  errors: BasicErrors = {
    checkJSONpath: false,
    checkProject: false
  };
  updateXMLresult: UpdateXML = {
    result: "",
    additionalMessage: "",
    icon: ""
  };
  surveyDetails: SurveyDetails;
  readyForStage2: boolean = false;
  readyForStage3: boolean = false;

  constructor(private appDataService: AppDataService, private formBuilder: FormBuilder) {
    this.projectForm = this.formBuilder.group({
      projectNumber: this.formBuilder.control('', Validators.compose([
        Validators.required,
        Validators.minLength(6)
      ])),
      projectPath: this.formBuilder.control('', Validators.compose([
        Validators.required,
        Validators.pattern(/^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$/g)
      ]))
    });
  }

  resetErrors(){
    this.errors = {
      checkProject: false,
      checkJSONpath: false
    };
  }

  setActive(stage: number): void {
    switch (stage) {
      case 1:
        this.activeStage = 1;
        break;
      case 2:
        if (this.readyForStage2)
          this.activeStage = 2;
        break;
      case 3:
        if (this.readyForStage3)
          this.activeStage = 3;
        break;
      default:
        break;
    }
  }

  ngOnInit() {
    this.loading = false;
    this.permissions = this.appDataService.checkPermissions().subscribe(result => {
      this.havePermission = result == "1" ? true : false;
      console.log(this.havePermission, result)
    });
  }

  // Need to unsubscribe from all observables on destroy
  ngOnDestroy(){
    this.basicSetup.unsubscribe();
    this.projectDetails.unsubscribe();
  }

  // When clicked Cancel in 2nd view. Need to return to 1st view
  detailsCancel() {
    this.activeStage = 1;
    this.readyForStage2 = false;
    this.readyForStage3 = false;
  };

  // When Confirmed is clicked in 2nd view. Need to move to 3rd view
  detailsConfirmed() {
    this.loading = true;
    this.completeView = this.appDataService.updateXML(this.projectForm.controls["projectNumber"].value, this.projectForm.controls["projectPath"].value).subscribe(result => {
      console.log(result);
      this.readyForStage3 = true;
      this.activeStage = 3;
      this.loading = false;

      this.updateXMLresult.result = result.status;
      this.updateXMLresult.additionalMessage = result.message;

      if (result.status === "error"){
        this.updateXMLresult.icon = "times";
      }
      else if (result.status === "decipher_error") {
        this.updateXMLresult.icon = "times";
      }
      else {
        this.updateXMLresult.icon = "check";
      }
    });
  };

  // When Next is clicked in 1st view
  submitBasic() {
    this.resetErrors();
    this.readyForStage2 = false;
    this.readyForStage3 = false;
    // Check if project exists
    // Check if URL file exists
    this.loading = true;
    this.basicSetup = this.appDataService.checkBasicSetup(this.projectForm.controls["projectNumber"].value, this.projectForm.controls["projectPath"].value).subscribe(data => 
      {
        console.log(data);
        if (data.checkProject === "not found"){
          this.errors.checkProject = true;
          this.loading = false;
        }
        if (data.checkJSONpath === "not found"){
          this.errors.checkJSONpath = true;
          this.loading = false;
        }
        if (data.checkJSONpath === "good" && data.checkProject === "good"){
          this.readyForStage2 = true;
          this.activeStage = 2;
          // Proceed to fetch parameters
          this.projectDetails = this.appDataService.getProjectDetails(this.projectForm.controls["projectNumber"].value).subscribe(project_details_data => {
            this.surveyDetails = project_details_data;
            this.loading = false;
          });
        }
      }
    );

    
  }


}
