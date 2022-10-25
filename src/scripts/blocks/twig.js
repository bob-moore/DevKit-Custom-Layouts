import { registerBlockType } from '@wordpress/blocks';
import apiFetch from '@wordpress/api-fetch';
import { BlockControls } from '@wordpress/components';
// import { InspectorControls } from '@wordpress/editor';
import { select } from '@wordpress/data';
import {Component, useLayoutEffect, useState} from "@wordpress/element";
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
import Select from "react-select";

const _editor = ( props ) =>
{
    const blockProps = useBlockProps(),
        { attributes, setAttributes } = props,
        { value } = attributes;

    return (
        <div {...blockProps}>
            <AceEditor
                mode="twig"
                theme="textmate"
                onChange={(value) => setAttributes({value: value})}
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
        </div>
    );
}
const _save = (props) =>
{
    return null;
}
registerBlockType( 'devkit-layouts/twig',
    {
        attributes : {
            value : {
                type: 'string',
                default : ''
            },
            preview : {
                type: 'bool',
                default : false
            }
        },
        edit : _editor,
        save : _save
    }
);