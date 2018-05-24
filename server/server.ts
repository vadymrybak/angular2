import * as express from 'express';
import {Application} from "express";
import checkProjectNumber from './checkProjectNumber';
import checkJSONpath from './checkJSONpath';
import getDetails from './getDetails';
import updateXML from './updateXML';


const app: Application = express();

const bodyParser = require('body-parser');

app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

app.route('/api/checkProjectNumber/:id').get(checkProjectNumber);
app.route('/api/checkJSONpath/:id').get(checkJSONpath);
app.route('/api/getDetails').get(getDetails);
app.route('/api/updateXML').get(updateXML);

const httpServer = app.listen(9000, () => {
    console.log("HTTP REST API Server running at http://localhost:" + httpServer.address().port);
});




