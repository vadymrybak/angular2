import { Injectable } from '@angular/core';
import {HttpClient, HttpParams, HttpHeaders} from "@angular/common/http";
import { Observable, forkJoin } from "rxjs";
import {map} from "rxjs/operators";

const httpOptions = {
    headers: new HttpHeaders({ 'Content-Type': 'application/json' })
  };
  
const links = {
    checkProject: "/api/checkProjectNumber/",
    checkJSONpath : "/api/checkJSONpath/"
};

@Injectable({
  providedIn: 'root'
})
export class AppDataService {

  constructor(private http:HttpClient) { }

  checkProjectNumber(): Observable<Response> {
    return this.http.get(links.checkProject).pipe(
        map(res => res['payload'])
    );
  };

  checkBasicSetup(): Observable<any>  {
    
    return forkJoin([
      this.http.get(links.checkProject).pipe(
        map(res => res['payload'])
      ),
      this.http.get(links.checkJSONpath).pipe(
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
