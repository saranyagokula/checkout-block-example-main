import metadata from './block.json';
import { ValidatedTextInput } from '@woocommerce/blocks-checkout';
import { __ } from '@wordpress/i18n';
import { useEffect, useState, useCallback } from '@wordpress/element';
import { extensionCartUpdate } from '@woocommerce/blocks-checkout';

// Global import
const { registerCheckoutBlock } = wc.blocksCheckout;

const Block = ({ children, checkoutExtensionData }) => { 
	const [ giftMessage, setGiftMessage ] = useState('');
    const { setExtensionData } = checkoutExtensionData;

    useEffect( () => {
        setExtensionData( 'checkout-block-example', 'gift_message', giftMessage  );

        wp.hooks.addAction( 'experimental__woocommerce_blocks-checkout-set-selected-shipping-rate', 'checkout-block-example', function( shipping ) {
            console.log( document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked').value  );
			var update_cart = extensionCartUpdate( {
                namespace: 'checkout-block-example',
                data: {
                    shipping_method: shipping.shippingRateId,
                    payment_method: document.querySelector('input[name="radio-control-wc-payment-method-options"]:checked').value
                },
            });
		} );  

        jQuery( document ).on('change', 'input[name="radio-control-wc-payment-method-options"]', function( e ) {
            console.log( 'here');
            var update_cart = extensionCartUpdate( {
                namespace: 'checkout-block-example',
                data: {
                    shipping_method: document.querySelector('input[name="radio-control-0"]:checked').value,
                    payment_method: e.target.value
                },
            });
        })

	}, [] );

    const onInputChange = useCallback(
		( value ) => {
			setGiftMessage( value );
			setExtensionData( 'checkout-block-example', 'gift_message', value );
		},
		[ setGiftMessage. setExtensionData ]
	)

    return (
        <div className={ 'example-fields' }> 
                <ValidatedTextInput
                        id="gift_message"
                        type="text"
                        required={false}
                        className={'gift-message'}
                        label={
                            'Gift Message'
                        }
                        value={ giftMessage }
                        onChange={ onInputChange }
                />
		</div>
    )
}

const options = {
	metadata,
	component: Block
};

registerCheckoutBlock( options );