import React, { useState, useEffect } from 'react';

// import ViewSelect from './views-select.js';
import ConditionDisplay from './condition-display.js';
// import ComparisonSelect from './comparison-select.js';
// import SubtypeSelect from './subtype-select.js';
// import TertiarySelect from './tertiary-select.js';
import Conditions from './conditions.js';

import OptionSearch from '../../includes/optionsearch.js';
import fieldKey from '../../includes/field-key';

class ConditionsGroups extends React.Component
{
    constructor(props)
    {
        super(props);

        this.state = {
            conditions : this.props.conditions,
			display : this.props.conditions.display,
			repeater : 1
        };

		this.fields = {
			display : devkit_metabox_data.fields.display
		}
    }
	_updateDisplay = ( select ) =>
	{
		let setValue = null;
		if ( typeof select === 'object' && select != null && select.hasOwnProperty( 'value' ) )
		{
			setValue = select.value;
		}

		this.setState( { display : setValue } );
	}
	_removeGroup = ( event ) => {
		event.preventDefault();
		if ( typeof this.props.onRemove !== 'undefined' )
		{
			this.props.onRemove( this.props.group );
		}
	}
	_removeCondition = ( i ) => {
		let conditions = this.state.conditions;
		conditions.conditions.splice( i, 1 );
		this.setState( { conditions : conditions, repeater : this.state.repeater + Math.random() } );
	}
	_addCondition = ( event ) => {
		event.preventDefault();
		let condition =
		{
			view : '__return_true',
			comparison : '=',
			subtype : '',
			deps : []
        }
		let conditions = this.state.conditions;
		conditions.conditions.push( condition );
		this.setState( { conditions : conditions } );
	}
	_updateCondition = ( i, condition ) =>
	{
		let conditions = this.state.conditions;
		conditions.conditions[i] = condition;
		this.setState( { conditions : conditions } );
	}
    render() {
        return (
			<fieldset className="conditions-group">
				<button className="devkit-button-icon button remove-control devkit-corner-remove-button" onClick={this._removeGroup}><span className="dashicons dashicons-no-alt"></span><span className="screen-reader-text">Remove Group</span></button>
				<div className="field-group">
					<div className="field flex-unset">
						<div className="field-group">
							<ConditionDisplay
								options={this.fields.display.options}
								name={fieldKey( 'conditions', this.props.group, 'display' )}
								value={OptionSearch( this.fields.display.options, this.state.conditions.display )}
								onChange={this._updateDisplay}
							/>
							<span className="condition-label">IF</span>
						</div>
					</div>
				</div>
				{ this.state.conditions.conditions.map( ( condition, i ) => {
					return(
						<React.Fragment key={i + this.state.repeater}>
							{ i > 0 &&
								<span className="group-label">And</span>
							}
							<div className="field-group">
								<Conditions
									strings={this.props.strings}
									group={this.props.group}
									index={i}
									conditions={condition}
									onRemove={this._removeCondition}
									onUpdate={this._updateCondition}
								/>
							</div>
						</React.Fragment>
					)
				})}
				<div className="field-group">
					<div className="field">
						<button className="devkit-link-button add group-control" onClick={this._addCondition}>New Condition</button>
					</div>
				</div>
            </fieldset>
        )
    }
}
export default ConditionsGroups;