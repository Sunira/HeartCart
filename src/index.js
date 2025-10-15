import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { Modal, Button, PanelBody, TextControl, TextareaControl, Notice } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';
import './style.scss';

const settings = window.wlbSettings || {};

if ( settings.root ) {
  apiFetch.use( apiFetch.createRootURLMiddleware( settings.root ) );
}

if ( settings.nonce ) {
  apiFetch.use( apiFetch.createNonceMiddleware( settings.nonce ) );
}

const NAMESPACE = 'wishlist/v1';

const fetchItems = async () => {
  const response = await apiFetch( { path: `${ NAMESPACE }/items` } );
  return response.items || [];
};

const saveItem = async ( item ) => {
  if ( item.id ) {
    return apiFetch( {
      path: `${ NAMESPACE }/items/${ item.id }`,
      method: 'POST',
      data: item,
    } );
  }

  return apiFetch( {
    path: `${ NAMESPACE }/items`,
    method: 'POST',
    data: item,
  } );
};

const deleteItem = async ( id ) => {
  return apiFetch( { path: `${ NAMESPACE }/items/${ id }`, method: 'DELETE' } );
};

const WishlistGrid = ( { items, onEdit, onDelete } ) => {
  if ( ! items.length ) {
    return <p role="status">{ __( 'No wishlist items yet.', 'wishlist-block' ) }</p>;
  }

  return (
    <div className="wlb-grid" role="list">
      { items.map( ( item ) => (
        <article key={ item.id } className="wlb-grid__item" role="listitem" aria-label={ item.title }>
          <header className="wlb-grid__header">
            <h3 className="wlb-grid__title">{ item.title }</h3>
          </header>
          <p className="wlb-grid__description">{ item.description }</p>
          <footer className="wlb-grid__footer">
            <Button variant="primary" onClick={ () => onEdit( item ) } aria-label={ __( 'Edit wishlist item', 'wishlist-block' ) }>
              { __( 'Edit', 'wishlist-block' ) }
            </Button>
            <Button variant="secondary" isDestructive onClick={ () => onDelete( item ) } aria-label={ __( 'Delete wishlist item', 'wishlist-block' ) }>
              { __( 'Delete', 'wishlist-block' ) }
            </Button>
          </footer>
        </article>
      ) ) }
    </div>
  );
};

const WishlistModal = ( { isOpen, onClose, onSave, item } ) => {
  const [ formState, setFormState ] = useState( item || { title: '', description: '', priority: 1 } );
  const [ errors, setErrors ] = useState( [] );

  useEffect( () => {
    setFormState( item || { title: '', description: '', priority: 1 } );
  }, [ item ] );

  const updateField = ( key ) => ( value ) => {
    setFormState( ( current ) => ( { ...current, [ key ]: value } ) );
  };

  const handleSubmit = async () => {
    if ( ! formState.title ) {
      setErrors( [ __( 'A title is required.', 'wishlist-block' ) ] );
      return;
    }

    setErrors( [] );
    await onSave( formState );
    onClose();
  };

  if ( ! isOpen ) {
    return null;
  }

  return (
    <Modal title={ item ? __( 'Edit Wishlist Item', 'wishlist-block' ) : __( 'Add Wishlist Item', 'wishlist-block' ) } onRequestClose={ onClose }>
      <PanelBody>
        <TextControl
          label={ __( 'Title', 'wishlist-block' ) }
          value={ formState.title }
          onChange={ updateField( 'title' ) }
          required
        />
        <TextareaControl
          label={ __( 'Description', 'wishlist-block' ) }
          value={ formState.description }
          onChange={ updateField( 'description' ) }
        />
      </PanelBody>
      { !! errors.length && (
        <Notice status="error" isDismissible={ false }>
          <ul>
            { errors.map( ( error ) => (
              <li key={ error }>{ error }</li>
            ) ) }
          </ul>
        </Notice>
      ) }
      <footer className="wlb-modal__footer">
        <Button variant="primary" onClick={ handleSubmit }>
          { __( 'Save', 'wishlist-block' ) }
        </Button>
        <Button variant="secondary" onClick={ onClose }>
          { __( 'Cancel', 'wishlist-block' ) }
        </Button>
      </footer>
    </Modal>
  );
};

registerBlockType( 'wishlist-block/wishlist', {
  title: __( 'Wishlist', 'wishlist-block' ),
  edit() {
    const [ items, setItems ] = useState( [] );
    const [ isModalOpen, setIsModalOpen ] = useState( false );
    const [ editingItem, setEditingItem ] = useState( null );
    const [ isLoading, setIsLoading ] = useState( true );
    const [ notice, setNotice ] = useState( null );

    useEffect( () => {
      let isMounted = true;
      fetchItems()
        .then( ( fetched ) => {
          if ( isMounted ) {
            setItems( fetched );
          }
        } )
        .catch( () => {
          if ( isMounted ) {
            setNotice( __( 'Unable to load wishlist items.', 'wishlist-block' ) );
          }
        } )
        .finally( () => {
          if ( isMounted ) {
            setIsLoading( false );
          }
        } );

      return () => {
        isMounted = false;
      };
    }, [] );

    const handleSave = async ( item ) => {
      const response = await saveItem( item );
      const savedItem = response.item || item;
      setItems( ( current ) => {
        const list = current.filter( ( entry ) => entry.id !== savedItem.id );
        return [ ...list, savedItem ].sort( ( a, b ) => a.priority - b.priority );
      } );
      setNotice( __( 'Wishlist updated.', 'wishlist-block' ) );
    };

    const handleDelete = async ( item ) => {
      if ( ! window.confirm( __( 'Are you sure you want to delete this item?', 'wishlist-block' ) ) ) {
        return;
      }

      await deleteItem( item.id );
      setItems( ( current ) => current.filter( ( entry ) => entry.id !== item.id ) );
      setNotice( __( 'Wishlist item deleted.', 'wishlist-block' ) );
    };

    const openModal = ( itemToEdit = null ) => {
      setEditingItem( itemToEdit );
      setIsModalOpen( true );
    };

    return (
      <div className="wlb-block-editor">
        <header className="wlb-toolbar" aria-label={ __( 'Wishlist toolbar', 'wishlist-block' ) }>
          <Button variant="primary" onClick={ () => openModal() }>
            { __( 'Add Item', 'wishlist-block' ) }
          </Button>
        </header>
        { notice && <Notice status="info" isDismissible={ false }>{ notice }</Notice> }
        { isLoading ? (
          <p role="status">{ __( 'Loading…', 'wishlist-block' ) }</p>
        ) : (
          <WishlistGrid items={ items } onEdit={ openModal } onDelete={ handleDelete } />
        ) }
        <WishlistModal
          isOpen={ isModalOpen }
          onClose={ () => setIsModalOpen( false ) }
          onSave={ handleSave }
          item={ editingItem }
        />
      </div>
    );
  },
  save() {
    return null;
  },
} );
