import * as express from 'express';
import {Application} from "express";
import checkProjectNumber from './checkProjectNumber';
import checkJSONpath from './checkJSONpath';


const app: Application = express();

const bodyParser = require('body-parser');

app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

app.route('/api/checkProjectNumber').get(checkProjectNumber);
app.route('/api/checkJSONpath').get(checkJSONpath);




const httpServer = app.listen(9000, () => {
    console.log("HTTP REST API Server running at http://localhost:" + httpServer.address().port);
});




