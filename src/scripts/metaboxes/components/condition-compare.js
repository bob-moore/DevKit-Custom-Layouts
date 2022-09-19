import React, { useState, useEffect } from 'react';
import Select from 'react-select'

import OptionSearch from '../../includes/optionsearch.js';

class ConditionCompare extends React.Component
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
            this.props.onChange( value, 'comparison' );
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
                    value={this.state.value}
                    onChange={this.handleChange}
                />
            </React.Fragment>
	    )
    }
}
export default ConditionCompare;