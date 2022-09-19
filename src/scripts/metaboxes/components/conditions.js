import React, { useState, useEffect } from 'react';

import OptionSearch from '../../includes/optionsearch.js';
import fieldKey from '../../includes/field-key';

import ConditionSelect from './condition-select';

class Conditions extends React.Component
{
    constructor(props)
    {
        super(props);

		this.fields = {
			archive : devkit_metabox_data.fields.archive,
            singular : devkit_metabox_data.fields.singular,
            view : devkit_metabox_data.fields.view,
            user : devkit_metabox_data.fields.user,
			taxonomy : devkit_metabox_data.fields.taxonomy,
            term : devkit_metabox_data.fields.term,
            posts : devkit_metabox_data.fields.posts,
            post_type : devkit_metabox_data.fields.post_type,
            author : devkit_metabox_data.fields.author,
            comparison : devkit_metabox_data.fields.comparison,
		};

		this.state = {
            conditions : this.props.conditions,
			view : this.props.conditions.view,
			comparison : this.props.conditions.comparison,
			subtype : this.props.conditions.subtype,
			deps : this.props.conditions.deps,
        };

		this.views_with_subtype = new Set( [ 'singular', 'archive', 'start', 'end', 'user' ] );
		this.views_with_deps = new Set( [ 'singular', 'archive' ] );
		this.subtype_with_deps = new Set( [ 'term', 'posts', 'post_type', 'author', 'taxonomy' ] );
    }
	_updateState = ( value, field ) => {
		/**
		 * Copy state for safe updating
		 */
		let state = this.state;
		/**
		 * Set specific condition
		 */
		state[field] = value;
		/**
		 * Update
		 */
		this.setState( state );
		/**
		 * Update group
		 */
		if ( typeof this.props.onUpdate !== 'undefined' )
		{
			this.props.onUpdate( this.props.index, this.state );
		}

	}

	_removeCondition = ( event ) => {
		event.preventDefault();
		if ( typeof this.props.onRemove !== 'undefined' )
		{
			this.props.onRemove( this.props.index );
		}
	}

    render() {
        return (
			<React.Fragment>
				<div className="field-group">
					<div className="field">
						<ConditionSelect
							label={this.fields.view.label}
							options={this.fields.view.options}
							name={fieldKey( 'conditions', this.props.group, 'conditions', this.props.index, 'view' )}
							value={OptionSearch( this.fields.view.options, this.state.view, false )}
							onChange={this._updateState}
							isClearable={true}
							field={'view'}
						/>
					</div>
					{ this.views_with_subtype.has( this.state.view ) &&
						<React.Fragment>
						<div className="field">
							<ConditionSelect
								label={this.fields.comparison.label}
								options={this.fields.comparison.options}
								name={fieldKey( 'conditions', this.props.group, 'conditions', this.props.index, 'comparison' )}
								value={OptionSearch( this.fields.comparison.options, this.state.comparison )}
								onChange={this._updateState}
								field={'comparison'}
							/>
						</div>
						<div className="field">
							<ConditionSelect
								label={this.fields[ this.state.view ].label}
								options={this.fields[ this.state.view ].options}
								name={fieldKey( 'conditions', this.props.group, 'conditions', this.props.index, 'subtype' )}
								value={OptionSearch( this.fields[ this.state.view ].options, this.state.subtype )}
								onChange={this._updateState}
								field={'subtype'}
							/>
						</div>
						</React.Fragment>
					}
					<div className="field flex-unset">
						<button className="devkit-button-icon button remove-control" onClick={this._removeCondition}><span className="dashicons dashicons-remove"></span><span className="screen-reader-text">Remove</span></button>
					</div>
					{ ( this.views_with_deps.has( this.state.view ) && this.subtype_with_deps.has( this.state.subtype ) ) &&
						<div className="field-group">
							<div className="field">
								<ConditionSelect
									label={this.fields[ this.state.subtype ].label}
									options={this.fields[ this.state.subtype ].options}
									name={fieldKey( 'conditions', this.props.group, 'conditions', this.props.index, 'deps' )}
									value={OptionSearch( this.fields[ this.state.subtype ].options, this.state.deps, false )}
									onChange={this._updateState}
									isClearable={true}
									isSearchable={true}
									isMulti={true}
									field={'deps'}
								/>
							</div>
						</div>
					}
					{ this.state.view === 'custom' &&
						<div className="field-group">
							<div className="field">
								<input
									type="text"
									name={fieldKey( 'conditions', this.props.group, 'conditions', this.props.index, 'condition_id' )}
									defaultValue={this.props.conditions.condition_id}
									placeholder='Condition ID'
								/>
							</div>
						</div>
					}
				</div>
			</React.Fragment>
        )
    }
}
export default Conditions;