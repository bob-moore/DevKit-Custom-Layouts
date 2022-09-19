import React, { useState, useEffect } from 'react';
import Select from 'react-select'

class ConditionSelect extends React.Component
{
    constructor(props)
    {
        super(props);
        this.state = {
            value : this.props.value,
            field : this.props.field
        };
    }
    handleChange = ( select ) => {
        this.setState( { value : select }, () => {
            let value = select === null ? '' : select.value;
            this.props.onChange( value, this.props.field );
        } );
    }
    componentDidUpdate(prevProps, prevState, snapshot )
    {
        if ( prevProps.options !== this.props.options )
        {
            let prev = false;

            if ( prevState.value instanceof Array )
            {
                prev = prevState.value;
            }
            else if ( prevState.value instanceof Object && prevState.value !== null )
            {
                prev = [ prevState.value ];
            }

            if ( prev )
            {
                let values = prev.filter( (el) => {
                    let contains = false;
                    for ( let i in this.props.options )
                    {
                        if ( this.props.options[i].value === el.value )
                        {
                            contains = true;
                            break;
                        }
                    }
                    return contains;
                } );
                if ( this.state.value instanceof Array === false )
                {
                    values = values.shift();
                }
                if ( values !== this.state.value )
                {
                    this.handleChange( values );
                }
            }
        }
    }
    render()
    {
        return (
            <React.Fragment>
                <label htmlFor={this.props.name} className="screen-reader-text">{this.props.label}</label>
                <Select
                    options={this.props.options}
                    name={this.props.isMulti ? false : this.props.name}
                    isClearable={this.props.isClearable || false}
                    isSearchable={this.props.isSearchable || false}
                    isMulti={this.props.isMulti || false}
                    value={this.state.value}
                    onChange={this.handleChange}
                />
                { this.props.isMulti && this.state.value &&
                    <React.Fragment>
                    { this.state.value.map( ( option, i ) => {
                        return(
                            <React.Fragment key={i + '_' + option.value}>
                                <input type="hidden" value={option.value} name={this.props.name + '[' + i +']'} readOnly={true}/>
                            </React.Fragment>
                        )
                    })}
                    </React.Fragment>
                }
            </React.Fragment>
	    )
    }
}
export default ConditionSelect;