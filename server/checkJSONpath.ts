import {Request, Response} from 'express';




export default function checkJSONpath (req: Request, res: Response) {

    var x = Math.floor((Math.random() * 10) + 1);
    
    //setTimeout(function() {
        res.status(200).json(
            {
                payload :  x > 1 ? "good" : "bad"
            }
        );
    //}, 1);

    

}