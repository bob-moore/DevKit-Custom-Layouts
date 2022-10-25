import { registerBlockType } from '@wordpress/blocks';

import Select from 'react-select'
import {useLayoutEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, attributes, setAttributes, isSelected } from '@wordpress/block-editor';

const _editor = ( props ) =>
{
    const blockProps = useBlockProps(),
        { attributes, setAttributes } = props,
        { template_part, options } = attributes,
        [ fetched, setFetched ] = useState(false)

    const _update = ( selected ) =>
    {
        setAttributes({template_part: selected})
        setAttributes({value: selected})
    }

    useLayoutEffect( () => {
        if ( ! fetched )
        {
            apiFetch( { path : 'devkit/layouts/v2/block/template-parts' } )
                .then( ( response ) =>
                {
                    setFetched(true);
                    setAttributes({options: response.options});
                }
            );
        }
    });

    return (
        <div {...blockProps}>
            <div style={{marginBottom : '1em'}}>
                <Select
                    options={options}
                    isClearable={false}
                    isSearchable={false}
                    isMulti={false}
                    value={template_part}
                    onChange={(selected) => setAttributes({template_part: selected})}
                />
            </div>
            <ServerSideRender
                block="devkit-layouts/template-parts"
                attributes={{
                    template_part : template_part,
                    preview : true
                }}
            />
        </div>
    );
}
const _save = (props) =>
{
    return null;
}
registerBlockType(
    'devkit-layouts/template-parts',
    {
        attributes : {
            template_part : {
                type: 'object',
                default : {}
            },
            preview : {
                type: 'bool',
                default : false
            },
            options : {
                type: 'array',
                default : []
            },
        },
        edit : _editor,
        save : _save
    }
);
