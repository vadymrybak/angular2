import { Component, OnInit } from '@angular/core';
import {AppDataService} from "./app-data.service";
import { Observable, Subscription } from 'rxjs';
import {NgForm} from '@angular/forms';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  activeStage = 1;
  loading: boolean = false;
  basicSetup: Subscription;
  errors = {
    checkProject: false,
    checkJSONpath: false
  };
  readyForStage2: boolean = true;
  readyForStage3: boolean = false;

  // Models
  projectNumber: string = "";
  jsonPath: string = "";

  constructor(private appDataService: AppDataService) {}

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
  }

  submitBasic() {
    this.resetErrors();
    this.readyForStage2 = false;
    this.readyForStage3 = false;
    // Check if project exists
    // Check if URL file exists
    this.loading = true;
    this.basicSetup = this.appDataService.checkBasicSetup().subscribe(data => 
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
        }
      }
    );

    
  }


}
