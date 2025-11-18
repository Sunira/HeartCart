(() => {
var registerBlockType = window.wp.blocks.registerBlockType;
var __ = window.wp.i18n.__;
var blockEditor = window.wp.blockEditor;
var InspectorControls = blockEditor.InspectorControls;
var MediaPlaceholder = blockEditor.MediaPlaceholder;
var MediaUpload = blockEditor.MediaUpload;
var RichText = blockEditor.RichText;
var URLInputButton = blockEditor.URLInputButton;
var useBlockProps = blockEditor.useBlockProps;
var Button = window.wp.components.Button;
var Flex = window.wp.components.Flex;
var FlexBlock = window.wp.components.FlexBlock;
var FlexItem = window.wp.components.FlexItem;
var PanelBody = window.wp.components.PanelBody;
var SelectControl = window.wp.components.SelectControl;
var TextControl = window.wp.components.TextControl;
var ToggleControl = window.wp.components.ToggleControl;
var element = window.wp.element;
var Fragment = element.Fragment;
var useEffect = element.useEffect;
var createElement = element.createElement;
var RichTextContent = RichText.Content;
var useBlockPropsSave = useBlockProps.save;

var DEFAULT_ITEM = {
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

function cloneItem(item) {
var source = item || DEFAULT_ITEM;
var image = source.image || {};
return {
title: source.title || '',
description: source.description || '',
url: source.url || '',
price: source.price || '',
priority: source.priority || 'medium',
image: {
id: image.id || 0,
url: image.url || '',
alt: image.alt || '',
},
};
}

function ensureItems(items) {
if (!Array.isArray(items)) {
return [];
}
return items.map(function (item) {
return cloneItem(item);
});
}

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
blockId: { type: 'string' },
columns: { type: 'number', default: 3 },
items: { type: 'array', default: [] },
showPrices: { type: 'boolean', default: true },
showPriorities: { type: 'boolean', default: true },
},
edit: function edit(props) {
var attributes = props.attributes || {};
var setAttributes = props.setAttributes;
var clientId = props.clientId;
var blockId = attributes.blockId;
var columns = typeof attributes.columns === 'number' ? attributes.columns : 3;
var showPrices = attributes.showPrices !== undefined ? attributes.showPrices : true;
var showPriorities = attributes.showPriorities !== undefined ? attributes.showPriorities : true;
var normalizedItems = ensureItems(attributes.items || []);

useEffect(
function () {
if (!blockId) {
setAttributes({ blockId: clientId });
}
},
[blockId, clientId],
);

function updateItem(index, nextValue) {
var nextItems = normalizedItems.map(function (item, itemIndex) {
var result = cloneItem(item);
if (itemIndex !== index) {
return result;
}
if (Object.prototype.hasOwnProperty.call(nextValue, 'title')) {
result.title = nextValue.title;
}
if (Object.prototype.hasOwnProperty.call(nextValue, 'description')) {
result.description = nextValue.description;
}
if (Object.prototype.hasOwnProperty.call(nextValue, 'url')) {
result.url = nextValue.url;
}
if (Object.prototype.hasOwnProperty.call(nextValue, 'price')) {
result.price = nextValue.price;
}
if (Object.prototype.hasOwnProperty.call(nextValue, 'priority')) {
result.priority = nextValue.priority;
}
if (Object.prototype.hasOwnProperty.call(nextValue, 'image')) {
var nextImage = nextValue.image || {};
result.image = {
id: nextImage.id || 0,
url: nextImage.url || '',
alt: nextImage.alt || '',
};
}
return result;
});
setAttributes({ items: nextItems });
}

function removeItem(index) {
var nextItems = normalizedItems
.filter(function (_, itemIndex) {
return itemIndex !== index;
})
.map(function (item) {
return cloneItem(item);
});
setAttributes({ items: nextItems });
}

function addItem() {
var nextItems = normalizedItems.map(function (item) {
return cloneItem(item);
});
nextItems.push(cloneItem(DEFAULT_ITEM));
setAttributes({ items: nextItems });
}

var blockProps = useBlockProps({
className: 'heartcart-wishlist heartcart-wishlist--columns-' + columns,
});

return createElement(
Fragment,
null,
createElement(
InspectorControls,
null,
createElement(
PanelBody,
{ title: __('Layout', 'heartcart'), initialOpen: true },
createElement(SelectControl, {
label: __('Columns', 'heartcart'),
value: columns,
onChange: function (value) {
setAttributes({ columns: Number(value) || 1 });
},
options: [
{ label: __('Single column', 'heartcart'), value: 1 },
{ label: __('Two columns', 'heartcart'), value: 2 },
{ label: __('Three columns', 'heartcart'), value: 3 },
{ label: __('Four columns', 'heartcart'), value: 4 },
],
}),
createElement(ToggleControl, {
label: __('Show prices', 'heartcart'),
checked: !!showPrices,
onChange: function (value) {
setAttributes({ showPrices: value });
},
}),
createElement(ToggleControl, {
label: __('Show priorities', 'heartcart'),
checked: !!showPriorities,
onChange: function (value) {
setAttributes({ showPriorities: value });
},
}),
),
),
createElement(
'div',
blockProps,
createElement(
'div',
{ className: 'heartcart-wishlist__header' },
createElement(
Button,
{ variant: 'primary', onClick: addItem },
__('Add wishlist item', 'heartcart'),
),
!normalizedItems.length
? createElement(
'p',
{ className: 'heartcart-wishlist__helper' },
__('Start building your wishlist by adding an item.', 'heartcart'),
)
: null,
),
createElement(
'div',
{ className: 'heartcart-wishlist-grid heartcart-wishlist-grid--editor' },
normalizedItems.map(function (item, index) {
var hasImage = item.image && item.image.url;
return createElement(
'div',
{ className: 'heartcart-wishlist-card heartcart-wishlist-card--editor', key: 'wishlist-item-' + index },
createElement(
'div',
{ className: 'heartcart-wishlist-card__image' },
hasImage
? createElement(MediaUpload, {
value: item.image.id,
onSelect: function (media) {
updateItem(index, {
image: {
id: media.id,
url: media.url,
alt: media.alt,
},
});
},
render: function (args) {
return createElement(
Button,
{ onClick: args.open, variant: 'secondary' },
__('Replace image', 'heartcart'),
);
},
})
: createElement(MediaPlaceholder, {
labels: {
title: __('Wishlist image', 'heartcart'),
description: __('Upload or select an image for this item.', 'heartcart'),
},
onSelect: function (media) {
updateItem(index, {
image: {
id: media.id,
url: media.url,
alt: media.alt,
},
});
},
}),
),
createElement(
'div',
{ className: 'heartcart-wishlist-card__fields' },
createElement(RichText, {
identifier: 'title',
tagName: 'h3',
className: 'heartcart-wishlist-card__title',
placeholder: __('Wishlist item title…', 'heartcart'),
value: item.title,
onChange: function (value) {
updateItem(index, { title: value });
},
allowedFormats: ['core/bold', 'core/italic', 'core/underline'],
}),
createElement(RichText, {
identifier: 'description',
tagName: 'p',
className: 'heartcart-wishlist-card__description',
placeholder: __('Write a short description…', 'heartcart'),
value: item.description,
onChange: function (value) {
updateItem(index, { description: value });
},
allowedFormats: ['core/bold', 'core/italic', 'core/strikethrough', 'core/link'],
}),
showPrices
? createElement(TextControl, {
label: __('Price', 'heartcart'),
value: item.price,
onChange: function (value) {
updateItem(index, { price: value });
},
placeholder: __('e.g. $199.00', 'heartcart'),
})
: null,
showPriorities
? createElement(SelectControl, {
label: __('Priority', 'heartcart'),
value: item.priority,
onChange: function (value) {
updateItem(index, { priority: value });
},
options: [
{ label: __('High', 'heartcart'), value: 'high' },
{ label: __('Medium', 'heartcart'), value: 'medium' },
{ label: __('Low', 'heartcart'), value: 'low' },
],
})
: null,
createElement(
Flex,
{ className: 'heartcart-wishlist-card__link-row', align: 'center', justify: 'flex-start' },
createElement(
FlexBlock,
null,
createElement(URLInputButton, {
label: __('Item URL', 'heartcart'),
url: item.url,
onChange: function (value) {
updateItem(index, { url: value });
},
}),
),
createElement(
FlexItem,
null,
createElement(
Button,
{ isDestructive: true, onClick: function () {
removeItem(index);
} },
__('Remove', 'heartcart'),
),
),
),
),
);
}),
),
);
},
save: function save(props) {
var attributes = props.attributes || {};
var columns = typeof attributes.columns === 'number' ? attributes.columns : 3;
var showPrices = attributes.showPrices !== undefined ? attributes.showPrices : true;
var showPriorities = attributes.showPriorities !== undefined ? attributes.showPriorities : true;
var blockId = attributes.blockId || 'instance';
var normalizedItems = ensureItems(attributes.items || []).filter(function (item) {
return (
item.title ||
item.description ||
(item.image && item.image.url) ||
item.url
);
});

if (!normalizedItems.length) {
return null;
}

var blockProps = useBlockPropsSave({
className: 'heartcart-wishlist heartcart-wishlist--columns-' + columns,
});

return createElement(
'div',
blockProps,
createElement(
'div',
{ className: 'heartcart-wishlist-grid' },
normalizedItems.map(function (item, index) {
var modalId = 'heartcart-wishlist-modal-' + blockId + '-' + index;
var modalTitleId = modalId + '-title';
var hasImage = item.image && item.image.url;

return createElement(
Fragment,
{ key: 'wishlist-item-' + index },
createElement(
'article',
{ className: 'heartcart-wishlist-card' },
createElement(
'a',
{ className: 'heartcart-wishlist-card__link', href: '#' + modalId },
hasImage
? createElement(
'div',
{ className: 'heartcart-wishlist-card__image' },
createElement('img', {
src: item.image.url,
alt: item.image.alt || item.title || __('Wishlist item image', 'heartcart'),
}),
)
: null,
createElement(
'div',
{ className: 'heartcart-wishlist-card__content' },
item.title
? createElement(RichTextContent, {
tagName: 'h3',
className: 'heartcart-wishlist-card__title',
value: item.title,
})
: null,
showPrices && item.price
? createElement(
'span',
{ className: 'heartcart-wishlist-card__price' },
item.price,
)
: null,
showPriorities && item.priority
? createElement(
'span',
{
className:
'heartcart-wishlist-card__priority heartcart-wishlist-card__priority--' + item.priority,
},
__('Priority', 'heartcart') + ': ' + item.priority,
)
: null,
item.description
? createElement(RichTextContent, {
tagName: 'p',
className: 'heartcart-wishlist-card__excerpt',
value: item.description,
})
: null,
createElement(
'span',
{ className: 'heartcart-wishlist-card__cta' },
__('View details', 'heartcart'),
),
),
),
),
createElement(
'div',
{ id: modalId, className: 'heartcart-wishlist-modal', role: 'dialog', 'aria-labelledby': modalTitleId },
createElement('a', {
href: '#',
className: 'heartcart-wishlist-modal__overlay',
'aria-label': __('Close wishlist modal', 'heartcart'),
}),
createElement(
'div',
{ className: 'heartcart-wishlist-modal__dialog', role: 'document' },
createElement(
'a',
{ href: '#', className: 'heartcart-wishlist-modal__close', 'aria-label': __('Close wishlist modal', 'heartcart') },
'×',
),
hasImage
? createElement(
'div',
{ className: 'heartcart-wishlist-modal__image' },
createElement('img', {
src: item.image.url,
alt: item.image.alt || item.title || __('Wishlist item image', 'heartcart'),
}),
)
: null,
createElement(
'div',
{ className: 'heartcart-wishlist-modal__content' },
item.title
? createElement(RichTextContent, {
tagName: 'h3',
id: modalTitleId,
className: 'heartcart-wishlist-modal__title',
value: item.title,
})
: null,
showPrices && item.price
? createElement(
'span',
{ className: 'heartcart-wishlist-modal__price' },
item.price,
)
: null,
showPriorities && item.priority
? createElement(
'span',
{
className:
'heartcart-wishlist-modal__priority heartcart-wishlist-modal__priority--' + item.priority,
},
__('Priority', 'heartcart') + ': ' + item.priority,
)
: null,
item.description
? createElement(RichTextContent, {
tagName: 'div',
className: 'heartcart-wishlist-modal__description',
value: item.description,
})
: null,
item.url
? createElement(
'a',
{
href: item.url,
target: '_blank',
rel: 'noopener noreferrer',
className: 'heartcart-wishlist-modal__button',
},
__('View item', 'heartcart'),
)
: null,
),
),
);
}),
),
);
},
});
})();
