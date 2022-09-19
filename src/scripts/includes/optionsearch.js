export default function( haystack, needle, useFallback = true )
{
    const _search = ( haystack, needle ) =>
    {
        let found = false;
        outer :
        for( let i in haystack )
        {
            /**
             * Check for nested optgroups in options
             */
            if ( typeof haystack[i].options !== 'undefined' )
            {
                sub :
                for ( let j in haystack[i].options )
                {
                    /**
                     * See if it matches our value passed in
                     */
                    if ( haystack[i].options[j].value == needle )
                    {
                        found = haystack[i].options[j];
                        break outer;
                    }
                }
            }
            else
            {
                /**
                 * See if it matches our value passed in
                 */
                if ( haystack[i].value == needle )
                {
                    found = haystack[i];
                    break outer;
                }
            }
        }
        return found;
    }

    const _default = ( haystack ) =>
    {
        let fallback = false;
        outer :
        for( let i in haystack )
        {
            /**
             * Check for nested optgroups in options
             */
            if ( typeof haystack[i].options !== 'undefined' )
            {
                sub :
                for ( let j in haystack[i].options )
                {
                    if ( ! fallback )
                    {
                        fallback = haystack[i].options[j];
                        break outer;
                    }
                }
            }
            else
            {
                if ( ! fallback )
                {
                    fallback = haystack[i];
                }
            }
        }
        return fallback;
    }

    let found =false;

    if ( needle instanceof Array )
    {
        /**
         * Map to option values
         */
        found = needle.map( (el) => {
            return _search( haystack, el );
        } )
        /**
         * Filter falsy values out
         */
        .filter( ( option ) => {
            return option;
        } );
        /**
         * maybe insert fallback
         */
        if ( ! found.length && useFallback )
        {
            found = [ _default( haystack ) ];
        }
    }
    else {

        found = _search( haystack, needle );

        if ( ! Object.keys(found).length && useFallback )
        {
            found = _default( haystack );
        }
    }
    return found;
}