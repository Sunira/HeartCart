import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
InspectorControls,
MediaPlaceholder,
MediaUpload,
RichText,
URLInputButton,
useBlockProps,
} from '@wordpress/block-editor';
import {
Button,
Flex,
FlexBlock,
FlexItem,
PanelBody,
SelectControl,
TextControl,
ToggleControl,
} from '@wordpress/components';
import { Fragment, useEffect } from '@wordpress/element';

import './editor.scss';
import './style.scss';

const DEFAULT_ITEM = {
title: '',
description: '',
url: '',
price: '',
priority: 'medium',
image: {
id: 0,
url: '',
alt: '',
},
};

const ensureItems = (items = []) =>
items.map((item) => {
const normalized = item || {};
const normalizedImage = normalized.image || {};
return {
...DEFAULT_ITEM,
...normalized,
image: {
...DEFAULT_ITEM.image,
...normalizedImage,
},
};
});

registerBlockType('heartcart/wishlist', {
apiVersion: 2,
title: __('HeartCart Wishlist', 'heartcart'),
description: __('Display a grid of wishlist items that expand to reveal details in a modal.', 'heartcart'),
category: 'widgets',
icon: 'heart',
supports: {
html: false,
align: ['wide', 'full'],
},
attributes: {
blockId: {
type: 'string',
},
columns: {
type: 'number',
default: 3,
},
items: {
type: 'array',
default: [],
},
showPrices: {
type: 'boolean',
default: true,
},
showPriorities: {
type: 'boolean',
default: true,
},
},

edit({ attributes, setAttributes, clientId }) {
const { blockId, columns, items = [], showPrices, showPriorities } = attributes;

useEffect(() => {
if (!blockId) {
setAttributes({ blockId: clientId });
}
}, [blockId, clientId, setAttributes]);

const normalizedItems = ensureItems(items);

const updateItem = (index, nextValue) => {
const nextItems = normalizedItems.map((item, itemIndex) =>
itemIndex === index ? { ...item, ...nextValue } : item,
);
setAttributes({ items: nextItems });
};

const removeItem = (index) => {
const nextItems = normalizedItems.filter((_, itemIndex) => itemIndex !== index);
setAttributes({ items: nextItems });
};

const addItem = () => {
setAttributes({ items: [...normalizedItems, { ...DEFAULT_ITEM }] });
};

const blockProps = useBlockProps({
className: `heartcart-wishlist heartcart-wishlist--columns-${columns}`,
});

return (
<Fragment>
<InspectorControls>
<PanelBody title={__('Layout', 'heartcart')} initialOpen>
<SelectControl
label={__('Columns', 'heartcart')}
value={columns}
onChange={(value) => setAttributes({ columns: Number(value) })}
options={[
{ label: __('Single column', 'heartcart'), value: 1 },
{ label: __('Two columns', 'heartcart'), value: 2 },
{ label: __('Three columns', 'heartcart'), value: 3 },
{ label: __('Four columns', 'heartcart'), value: 4 },
]}
/>
<ToggleControl
label={__('Show prices', 'heartcart')}
checked={!!showPrices}
onChange={(value) => setAttributes({ showPrices: value })}
/>
<ToggleControl
label={__('Show priorities', 'heartcart')}
checked={!!showPriorities}
onChange={(value) => setAttributes({ showPriorities: value })}
/>
</PanelBody>
</InspectorControls>

<div {...blockProps}>
<div className="heartcart-wishlist__header">
<Button variant="primary" onClick={addItem}>
{__('Add wishlist item', 'heartcart')}
</Button>
{!normalizedItems.length && (
<p className="heartcart-wishlist__helper">
{__('Start building your wishlist by adding an item.', 'heartcart')}
</p>
)}
</div>

<div className="heartcart-wishlist-grid heartcart-wishlist-grid--editor">
{normalizedItems.map((item, index) => (
<div className="heartcart-wishlist-card heartcart-wishlist-card--editor" key={`wishlist-item-${index}`}>
<div className="heartcart-wishlist-card__image">
{item.image && item.image.url ? (
<MediaUpload
value={item.image.id}
onSelect={(media) =>
updateItem(index, {
image: {
id: media.id,
url: media.url,
alt: media.alt,
},
})
}
render={({ open }) => (
<Button onClick={open} variant="secondary">
{__('Replace image', 'heartcart')}
</Button>
)}
/>
) : (
<MediaPlaceholder
labels={{
title: __('Wishlist image', 'heartcart'),
description: __('Upload or select an image for this item.', 'heartcart'),
}}
onSelect={(media) =>
updateItem(index, {
image: {
id: media.id,
url: media.url,
alt: media.alt,
},
})
}
/>
)}
</div>
<div className="heartcart-wishlist-card__fields">
<RichText
identifier="title"
tagName="h3"
className="heartcart-wishlist-card__title"
placeholder={__('Wishlist item title…', 'heartcart')}
value={item.title}
onChange={(value) => updateItem(index, { title: value })}
allowedFormats={['core/bold', 'core/italic', 'core/underline']}
/>
<RichText
identifier="description"
tagName="p"
className="heartcart-wishlist-card__description"
placeholder={__('Write a short description…', 'heartcart')}
value={item.description}
onChange={(value) => updateItem(index, { description: value })}
allowedFormats={['core/bold', 'core/italic', 'core/strikethrough', 'core/link']}
/>
{showPrices && (
<TextControl
label={__('Price', 'heartcart')}
value={item.price}
onChange={(value) => updateItem(index, { price: value })}
placeholder={__('e.g. $199.00', 'heartcart')}
/>
)}
{showPriorities && (
<SelectControl
label={__('Priority', 'heartcart')}
value={item.priority}
onChange={(value) => updateItem(index, { priority: value })}
options={[
{ label: __('High', 'heartcart'), value: 'high' },
{ label: __('Medium', 'heartcart'), value: 'medium' },
{ label: __('Low', 'heartcart'), value: 'low' },
]}
/>
)}
<Flex className="heartcart-wishlist-card__link-row" align="center" justify="flex-start">
<FlexBlock>
<URLInputButton
label={__('Item URL', 'heartcart')}
url={item.url}
onChange={(value) => updateItem(index, { url: value })}
/>
</FlexBlock>
<FlexItem>
<Button isDestructive onClick={() => removeItem(index)}>
{__('Remove', 'heartcart')}
</Button>
</FlexItem>
</Flex>
</div>
</div>
))}
</div>
</div>
</Fragment>
);
},

save({ attributes }) {
const { blockId, columns, items = [], showPrices, showPriorities } = attributes;
const normalizedItems = ensureItems(items).filter((item) =>
item.title || item.description || item.image.url || item.url,
);

if (!normalizedItems.length) {
return null;
}

const blockProps = useBlockProps.save({
className: `heartcart-wishlist heartcart-wishlist--columns-${columns}`,
});

return (
<div {...blockProps}>
<div className="heartcart-wishlist-grid">
{normalizedItems.map((item, index) => {
const modalId = `heartcart-wishlist-modal-${blockId || 'instance'}-${index}`;
const modalTitleId = `${modalId}-title`;

return (
<Fragment key={`wishlist-item-${index}`}>
<article className="heartcart-wishlist-card">
<a className="heartcart-wishlist-card__link" href={`#${modalId}`}>
{item.image && item.image.url && (
<div className="heartcart-wishlist-card__image">
<img src={item.image.url} alt={item.image.alt || item.title || __('Wishlist item image', 'heartcart')} />
</div>
)}
<div className="heartcart-wishlist-card__content">
{item.title && (
<RichText.Content tagName="h3" className="heartcart-wishlist-card__title" value={item.title} />
)}
{showPrices && item.price && (
<span className="heartcart-wishlist-card__price">{item.price}</span>
)}
{showPriorities && item.priority && (
<span className={`heartcart-wishlist-card__priority heartcart-wishlist-card__priority--${item.priority}`}>
{__('Priority', 'heartcart')}: {item.priority}
</span>
)}
{item.description && (
<RichText.Content tagName="p" className="heartcart-wishlist-card__excerpt" value={item.description} />
)}
<span className="heartcart-wishlist-card__cta">{__('View details', 'heartcart')}</span>
</div>
</a>
</article>

<div id={modalId} className="heartcart-wishlist-modal" role="dialog" aria-labelledby={modalTitleId}>
<a href="#" className="heartcart-wishlist-modal__overlay" aria-label={__('Close wishlist modal', 'heartcart')} />
<div className="heartcart-wishlist-modal__dialog" role="document">
<a href="#" className="heartcart-wishlist-modal__close" aria-label={__('Close wishlist modal', 'heartcart')}>
&times;
</a>
{item.image && item.image.url && (
<div className="heartcart-wishlist-modal__image">
<img src={item.image.url} alt={item.image.alt || item.title || __('Wishlist item image', 'heartcart')} />
</div>
)}
<div className="heartcart-wishlist-modal__content">
{item.title && (
<RichText.Content tagName="h3" id={modalTitleId} value={item.title} className="heartcart-wishlist-modal__title" />
)}
{showPrices && item.price && (
<span className="heartcart-wishlist-modal__price">{item.price}</span>
)}
{showPriorities && item.priority && (
<span className={`heartcart-wishlist-modal__priority heartcart-wishlist-modal__priority--${item.priority}`}>
{__('Priority', 'heartcart')}: {item.priority}
</span>
)}
{item.description && (
<RichText.Content tagName="div" className="heartcart-wishlist-modal__description" value={item.description} />
)}
{item.url && (
<a
href={item.url}
target="_blank"
rel="noopener noreferrer"
className="heartcart-wishlist-modal__button"
>
{__('View item', 'heartcart')}
</a>
)}
</div>
</div>
</div>
</Fragment>
);
})}
</div>
</div>
);
},
});
