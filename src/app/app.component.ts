import { Component, OnInit } from '@angular/core';
import {AppDataService} from "./app-data.service";
import { Observable, Subscription } from 'rxjs';
import {NgForm} from '@angular/forms';
import SurveyDetails from './models/survey-details';
import BasicErrors from './models/basic-errors';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  activeStage = 1;
  loading: boolean = false;

  basicSetup: Subscription;
  projectDetails: Subscription;
  completeView: Subscription;

  errors: BasicErrors = {
    checkJSONpath: false,
    checkProject: false
  };
  surveyDetails: SurveyDetails;
  readyForStage2: boolean = true;
  readyForStage3: boolean = false;

  // Models
  projectNumber: string = "";
  jsonPath: string = "";

  constructor(private appDataService: AppDataService) {
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
    this.completeView = this.appDataService.updateXML(this.projectNumber, this.jsonPath).subscribe(result => {
      console.log(result);
      this.readyForStage3 = true;
      this.activeStage = 3;
      this.loading = false;
    });
  };

  submitBasic() {
    this.resetErrors();
    this.readyForStage2 = false;
    this.readyForStage3 = false;
    // Check if project exists
    // Check if URL file exists
    this.loading = true;
    this.basicSetup = this.appDataService.checkBasicSetup(this.projectNumber, this.jsonPath).subscribe(data => 
      {
        console.log(data);
        this.loading = false;
        if (data.checkProject === "bad"){
          this.errors.checkProject = true;
        }
        if (data.checkJSONpath === "bad"){
          this.errors.checkJSONpath = true;
        }
        if (data.checkJSONpath === "good" && data.checkProject === "good"){
          this.readyForStage2 = true;
          this.activeStage = 2;
          // Proceed to fetch parameters
          this.projectDetails = this.appDataService.getProjectDetails(this.projectNumber).subscribe(project_details_data => {
            this.surveyDetails = project_details_data;
          });
        }
      }
    );

    
  }


}
