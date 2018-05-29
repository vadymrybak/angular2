import {Request, Response} from 'express';


export default function HavePermissions (req: Request, res: Response) {

    var x = Math.floor((Math.random() * 2) + 1);

    res.status(200).json(
        {
            payload :  x > 1 ? "1" : "0"
        }
    );

}