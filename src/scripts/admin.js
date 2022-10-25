
// noinspection JSUnresolvedVariable

import domReady from '@wordpress/dom-ready';
import apiFetch from "@wordpress/api-fetch";
import { createElement, render } from "@wordpress/element";
import ScriptsMetabox from './metaboxes/code/metabox';
import OptionsMetabox from './metaboxes/options/metabox';
import LocationsMetabox from "./metaboxes/locations/metabox";
import ConditionsMetabox from "./metaboxes/conditions/metabox";

domReady( () => {

    let metaboxes = [
        {
            container : document.getElementById('devkit-layouts-metabox-options'),
            element : OptionsMetabox
        },
        {
            container : document.getElementById('devkit-layouts-metabox-scripts'),
            element : ScriptsMetabox
        },
        {
            container : document.getElementById('devkit-layouts-metabox-locations'),
            element : LocationsMetabox
        },
        {
            container : document.getElementById('devkit-layouts-metabox-conditions'),
            element : ConditionsMetabox
        }
    ]
    apiFetch( { path : 'devkit/layouts/v2/metabox/' + devkit_post_id } ).then( ( response ) =>
        {
            for ( let i in metaboxes )
            {
                if ( metaboxes[i].container )
                {
                    render(
                        createElement( metaboxes[i].element, {
                            meta : response.meta,
                            fields : response.fields,
                        } ) ,
                        metaboxes[i].container
                    );
                }
            }
        }
    );
} );