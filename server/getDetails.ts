import {Request, Response} from 'express';

const output = {
    creator: "Vadym Rybak",
    projectNumber: "53b/1803159",
    projectName: "Testing Angular 6",
    lastEditDate: "2018-03-27 18:10:13",
    xml: "<survey state='tesing'>hello</survey>"
};


export default function getDetails (req: Request, res: Response) {
    
    //setTimeout(function() {
        res.status(200).json(
            {
                payload :  output
            }
        );
    //}, 1);

    

}