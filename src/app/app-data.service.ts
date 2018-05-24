import { Injectable } from '@angular/core';
import {HttpClient, HttpParams, HttpHeaders} from "@angular/common/http";
import { Observable, forkJoin } from "rxjs";
import {map} from "rxjs/operators";
import SurveyDetails from "./models/survey-details";
import BasicErrors from "./models/basic-errors";

const httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };

// LIVE LINKS
// const links = {
//   checkProject: "data/checkProject.php?pnum=",
//   checkJSONpath : "data/checkJSON.php?jpath=",
//   getDetails: "data/getDetails.php",
//   updateXML: "/api/updateXML"
// };

// TEST LINKS
const links = {
    checkProject: "/api/checkProjectNumber/",
    checkJSONpath : "/api/checkJSONpath/",
    getDetails: "/api/getDetails",
    updateXML: "/api/updateXML"
};

@Injectable({
  providedIn: 'root'
})
export class AppDataService {

  constructor(private http:HttpClient) { }

  // Gets project details
  getProjectDetails(projectNumber: string): Observable<SurveyDetails> {
    return this.http.get(links.getDetails).pipe(
      map(res => res['payload'])
    );
  };

  // Updates project's XML
  updateXML(projectNumber: string, jsonPath: string): Observable<Response> {
    return this.http.get(links.updateXML).pipe(
      map(res => res['payload'])
    );
  };

  // Checks of project and JSON file exist
  checkBasicSetup(projectNumber: string, JSONpath: string): Observable<any>  {
    return forkJoin([
      this.http.get(`${links.checkProject}${projectNumber}`).pipe(
        map(res => res['payload'])
      ),
      this.http.get(`${links.checkJSONpath}${JSONpath}`).pipe(
        map(res => res['payload'])
      )
    ])
    .pipe(
      map((data: any[]) => {
          let response: Object = {
            checkProject: data[0],
            checkJSONpath: data[1]
          };
          return response;
        }
      )
    );
  } 


}
