import React, { useState, useEffect } from 'react';
import Select from 'react-select'

// import OptionSearch from '../../includes/optionsearch.js';
// import fieldKey from '../includes/field-key';

class ConditionDisplay extends React.Component
{
    constructor(props)
    {
        super(props);
        this.state = {
            value : this.props.value
        };
    }
    handleChange = ( select ) => {
        this.setState( { value : select }, () => {
            let value = select === null ? '' : select.value;
            this.props.onChange( value );
        } );
    }
    render() {
        return (
            <React.Fragment>
                <label htmlFor={this.props.name} className="screen-reader-text">{this.props.label}</label>
                <Select
                    options={this.props.options}
                    name={this.props.name}
                    isClearable={false}
                    isSearchable={false}
                    onChange={this.handleChange}
                    value={this.state.value}
                />
            </React.Fragment>
	    )
    }
}
export default ConditionDisplay;