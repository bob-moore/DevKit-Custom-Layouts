import { registerBlockType } from '@wordpress/blocks';
import apiFetch from '@wordpress/api-fetch';
import { BlockControls } from '@wordpress/components';
// import { InspectorControls } from '@wordpress/editor';
import { select } from '@wordpress/data';
import { Component } from "@wordpress/element";
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';
import AceEditor from "react-ace";

import { useBlockProps, attributes, setAttributes, isSelected } from '@wordpress/block-editor';

import "ace-builds/src-noconflict/mode-twig";
import "ace-builds/src-noconflict/mode-scss";
import "ace-builds/src-noconflict/mode-javascript";
import "ace-builds/src-noconflict/theme-textmate";
import "ace-builds/src-noconflict/ext-language_tools";
import "ace-builds/src-noconflict/ext-beautify";
import "ace-builds/src-noconflict/snippets/html";
import "ace-builds/src-noconflict/snippets/twig";
import "ace-builds/src-noconflict/snippets/javascript";
import "ace-builds/src-noconflict/ext-emmet";
import 'emmet-core';

class DevkitCodeBlock extends Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            code : ''
        }
        console.log(this.state);
    }


    _update = ( value ) => {
        this.props.setAttributes({code: value});
    }

    render() {
        return (
            <p>Hello World</p>
            // <div>
            //     <AceEditor
            //         mode="twig"
            //         theme="textmate"
            //         onChange={this._update}
            //         width="100%"
            //         minLines={10}
            //         maxLines={200}
            //         defaultValue={this.state.code}
            //         fontSize={16}
            //         showPrintMargin={true}
            //         showGutter={true}
            //         highlightActiveLine={true}
            //         setOptions={{
            //             enableBasicAutocompletion: true,
            //             enableLiveAutocompletion: true,
            //             enableSnippets: true,
            //             showInvisibles : false,
            //             displayIndentGuides : true,
            //             enableEmmet : true,
            //             wrapBehavioursEnabled : true
            //         }}
            //     />
            // </div>
        )
    }
}

registerBlockType( 'devkit-layouts/custom-code', {
    attributes : {
        value : {
            type: 'string',
            default : ''
        },
        preview : {
            type: 'bool',
            default : false
        },
        widget : 'array',
            default : []
    },
    // edit: DevkitCodeBlock,
    // save: ( props ) => {
    //     return (
    //         <p>Do Code Here</p>
    //     )
    // },
    edit: ( { attributes, setAttributes, isSelected, editMode } ) => {
        const blockProps = useBlockProps();
        const { value } = attributes;
        const _update = ( value ) => {
            setAttributes({value: value})
        }
        return (
            <div {...blockProps}>
                { ! isSelected ?
                    <ServerSideRender
                        block="devkit-layouts/custom-code"
                        attributes={{
                            value : value,
                            preview : true
                        }}
                    />
                    :
                    <AceEditor
                        mode="twig"
                        theme="textmate"
                        onChange={_update}
                        width="100%"
                        minLines={10}
                        maxLines={200}
                        defaultValue={value}
                        fontSize={16}
                        showPrintMargin={true}
                        showGutter={true}
                        highlightActiveLine={true}
                        setOptions={{
                            enableBasicAutocompletion: true,
                            enableLiveAutocompletion: true,
                            enableSnippets: true,
                            showInvisibles : false,
                            displayIndentGuides : true,
                            enableEmmet : true,
                            wrapBehavioursEnabled : true
                        }}
                    />
                }

            </div>
        );
    },
    save: ( {attributes} ) => {
        const blockProps = useBlockProps.save();
        const { value } = attributes;
        return null;
    }
} );