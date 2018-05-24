import {Request, Response} from 'express';

export default function updateXML (req: Request, res: Response) {
    
    //setTimeout(function() {
        res.status(200).json(
            {
                payload :  "updated"
            }
        );
    //}, 1);

    

}