import React, { useState, useEffect } from 'react';
import Select from 'react-select'

import OptionSearch from '../../includes/optionsearch.js';

class TertiarySelect extends React.Component
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
            this.props.onChange( value, deps );
        } );
    }
    componentDidUpdate(prevProps, prevState, snapshot) {
        if ( prevProps.options !== this.props.options )
        {
            let values = prevState.value.filter( (el) => {
                return this.props.options.includes( el );
            } );

            if ( values !== this.state.value )
            {
                this.changeHandler( values );
            }
        }
    }
    render() {
        return (
            <React.Fragment>
                <label htmlFor={this.props.fieldkey + '[conditions][' + this.props.groupkey + '][' + this.props.index + '][selection]'} className="screen-reader-text">{this.props.label}</label>
                <Select
                    options={this.props.options}
                    name={this.props.fieldkey + '[conditions][' + this.props.groupkey + '][' + this.props.index + '][selection]'}
                    isClearable={true}
                    isSearchable={false}
                    isMulti={true}
                    value={this.state.value}
                    onChange={this.changeHandler}
                />
            </React.Fragment>
	    )
    }
}
export default TertiarySelect;