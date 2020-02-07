import React from 'react'
import { useSelector, useDispatch } from 'react-redux'
import {
  updatePendingBlocklist,
  // updatePendingUnregisteredClientsForDeletion
} from './store/actions'
import { blocklistSelector } from './store/reducers'
import PropTypes from 'prop-types'
import styles from './UnregisteredClientsView.module.css'
import sharedStyles from './App.module.css'
import classnames from 'classnames'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import {
  faCheckSquare,
  faThumbsUp } from '@fortawesome/free-solid-svg-icons'
import {
    faSquare } from '@fortawesome/free-regular-svg-icons'
import get from 'lodash/get'
import size from 'lodash/size'
import isEqual from 'lodash/isEqual'

export default function UnregisteredClientsView(props) {
  const dispatch = useDispatch()
  const blocklist = useSelector(state => {
    if( null !== state.blocklistUpdateStatus.pending ) {
      return state.blocklistUpdateStatus.pending
    } else {
      return blocklistSelector(state) 
    }
  })
  const detectedUnregisteredClients = size(Object.keys(props.clients)) > 0
  const allDetectedConflictsSelectedForBlocking = 
              isEqual(Object.keys(props.clients).sort(), [...(blocklist || [])].sort())
  const allDetectedConflicts = Object.keys(props.clients)

  function isCheckedForBlocking(md5) {
    return !! blocklist.find(x => x === md5)
  }

  function changeCheckForBlocking(md5, allDetectedConflicts) {
    const newBlocklist = 'all' === md5
      ? allDetectedConflictsSelectedForBlocking
        ? [] // uncheck them all
        : allDetectedConflicts // check them all
      : isCheckedForBlocking(md5)
        ? blocklist.filter(x => x !== md5)
        : [...blocklist, md5]
    
    dispatch(updatePendingBlocklist(newBlocklist))
  }

  return <div className={ classnames(styles['unregistered-clients'], { [styles['none-detected']]: !detectedUnregisteredClients }) }>
    <h3 className={ sharedStyles['section-title'] }>Other themes or plugins</h3>
    {detectedUnregisteredClients
      ? <div>
          <p className={sharedStyles['explanation']}>
            Below is the list of other versions of Font Awesome from active
            plugins or themes that are loading on your site. Check off any that
            you would like to block from loading. Normally this just blocks the
            conflicting version of Font Awesome and doesn't affect the other
            functions of the plugin, but you should verify your site works as expected.
          </p>
          <div>
            <input
              id='block_all_detected_conflicts'
              name='block_all_detected_conflicts'
              type="checkbox"
              value='all'
              checked={ allDetectedConflictsSelectedForBlocking }
              onChange={ () => changeCheckForBlocking('all', allDetectedConflicts) }
              className={ classnames(sharedStyles['sr-only'], sharedStyles['input-checkbox-custom']) }
            />
            <label htmlFor='block_all_detected_conflicts' className={ styles['checkbox-label'] }>
              <span className={ sharedStyles['relative'] }>
                <FontAwesomeIcon
                  icon={ faCheckSquare }
                  className={ sharedStyles['checked-icon'] }
                  size="lg"
                  fixedWidth
                />
                <FontAwesomeIcon
                  icon={ faSquare }
                  className={ sharedStyles['unchecked-icon'] }
                  size="lg"
                  fixedWidth
                />
              </span>
              All
            </label>
          </div>
          <table className={classnames('widefat', 'striped')}>
            <tbody>
            <tr className={sharedStyles['table-header']}>
              <th>Block</th>
              <th>Type</th>
              <th>URL</th>
            </tr>
            {
              allDetectedConflicts.map(md5 => (
                <tr key={md5}>
                  <td>
                    <input
                      id={`block_${md5}`}
                      name={`block_${md5}`}
                      type="checkbox"
                      value={ md5 }
                      checked={ isCheckedForBlocking(md5) }
                      onChange={ () => changeCheckForBlocking(md5) }
                      className={ classnames(sharedStyles['sr-only'], sharedStyles['input-checkbox-custom']) }
                    />
                    <label htmlFor={`block_${md5}`} className={ styles['checkbox-label'] }>
                      <span className={ sharedStyles['relative'] }>
                        <FontAwesomeIcon
                          icon={ faCheckSquare }
                          className={ sharedStyles['checked-icon'] }
                          size="lg"
                          fixedWidth
                        />
                        <FontAwesomeIcon
                          icon={ faSquare }
                          className={ sharedStyles['unchecked-icon'] }
                          size="lg"
                          fixedWidth
                        />
                      </span>
                    </label>
                  </td>
                  <td>
                    {get(props.clients[md5], 'tagName', 'unknown').toLowerCase()}
                  </td>
                  <td>
                    {props.clients[md5].src || props.clients[md5].href || get(props.clients[md5], 'excerpt') || <em>in page source</em>}
                  </td>
                </tr>
              ))
            }
            </tbody>
          </table>
        </div>
      : <div className={ classnames(sharedStyles['explanation'], sharedStyles['flex'], sharedStyles['flex-row'] )}>
          <div>
            <FontAwesomeIcon icon={ faThumbsUp } size='lg'/>
          </div>
          <div className={ sharedStyles['space-left'] }>
            We haven't detected any plugins or themes trying to load Font Awesome.
          </div>
      </div>
    }
  </div>
}

UnregisteredClientsView.propTypes = {
  clients: PropTypes.object.isRequired
}
