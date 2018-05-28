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
  activeStage = 1;
  loading: boolean = false;
  projectForm: FormGroup;

  basicSetup: Subscription;
  projectDetails: Subscription;
  completeView: Subscription;

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

  // Models
  projectNumber: string = "";
  jsonPath: string = "";

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
    // this.projectForm.get('projectNumber').valueChanges.subscribe(val => {
    //   console.log(this.projectForm.controls["projectNumber"]);
    // });
  }

  ngOnDestroy(){
    this.basicSetup.unsubscribe();
    this.projectDetails.unsubscribe();
  }

  detailsCancel() {
    this.activeStage = 1;
    this.readyForStage2 = false;
    this.readyForStage3 = false;
  };

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
        if (data.checkProject === "bad"){
          this.errors.checkProject = true;
          this.loading = false;
        }
        if (data.checkJSONpath === "bad"){
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
