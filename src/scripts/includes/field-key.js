export default function(...args)
{
    if ( typeof devkit_metabox_data === 'undefined' )
    {
        return false;
    }
    let parts = Array.from( args );
    /**
     * Add the key to the front fo the array
     */
    parts.unshift( devkit_metabox_data.key );
    /**
     * Wrap all parts except first (key) in parens
     */
    let field = parts.reduce( ( previous, current, index ) => {
        return previous + '[' + current + ']';
    } );

    return field;
}