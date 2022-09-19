import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import domReady from '@wordpress/dom-ready';

// import fieldKey from '../includes/field-key';
import ConditionsGroups from './components/conditions-groups.js';

class ConditionsMetabox extends React.Component
{
    constructor(props)
    {
        super(props);
        this.state = {
            conditions : devkit_metabox_data.meta.conditions,
            repeater : 1
        };
    }

    _update = ( component ) => {

    }

    _removeGroup = ( i ) => {
        let conditions = this.state.conditions;
		conditions.splice( i, 1 );
        console.log(conditions);
		this.setState( { conditions : conditions, repeater : this.state.repeater + Math.random() } );
    }
    _addGroup = ( e ) => {
        e.preventDefault();
        let group = {
            display: 1,
            conditions : [
                {
                    view : '__return_true',
                    comparison : '=',
                    subtype : '',
                    deps : []
                }

            ]
        };
        let conditions = this.state.conditions;
        conditions.push( group );
        this.setState( { conditions : conditions } );
    }

    render() {
        return (
            <React.Fragment>
            { ! this.state.conditions.length &&
                <div className="field-group">
                    <div className="field">
                        <div className="devkit-notice notice-error notice">
                            <p>This layout has no display conditions. <a href='#' onClick={this._addGroup}>Create one now</a></p>
                        </div>
                    </div>
                </div>
            }
            { this.state.conditions.map( ( group, i ) => {
                return (
                    <React.Fragment key={i + this.state.repeater}>
                        { i > 0 &&
                            <span className="group-label">OR</span>
                        }
                        <ConditionsGroups
                            strings={this.props.strings}
                            group={i}
                            conditions={this.state.conditions[i]}
                            onRemove={this._removeGroup}
                        />
                    </React.Fragment>
                )
            })}
            <div className="field-group">
                <div className="field push-right">
                     <button className="button devkit-button button-primary add group-control" onClick={this._addGroup}>Add Condition Group</button>
                </div>
            </div>
            </React.Fragment>
        )
    }
}

domReady( function () {

	let metabox_container = document.getElementById('devkit-layouts-metabox-conditions');

	if ( metabox_container )
	{
		const root = ReactDOM.createRoot( metabox_container );
		root.render(
            <ConditionsMetabox
                strings={devkit_metabox_data.strings}
                fields={
                    {
                        archive : devkit_metabox_data.fields.archive,
                        singular : devkit_metabox_data.fields.singular,
                        view : devkit_metabox_data.fields.view,
                        user : devkit_metabox_data.fields.user,
                        term : devkit_metabox_data.fields.term,
                        posts : devkit_metabox_data.fields.posts,
                        post_type : devkit_metabox_data.fields.post_type,
                        author : devkit_metabox_data.fields.author,
                        show : devkit_metabox_data.fields.show,
                        comparison : devkit_metabox_data.fields.comparison,
                    }
                }
            />
        );
	}

} );