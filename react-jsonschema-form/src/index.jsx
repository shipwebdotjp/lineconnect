import * as React from "react";
// import { createRoot } from 'react-dom/client';
import { render } from 'react-dom';
import RJSFForm from './component/RJSFForm';


const container = document.getElementById('app');
render(<RJSFForm />, container);
//const root = createRoot(container);

//root.render(
//    <RJSFForm />
//);