import { assert } from '@/core'
import { BaseService } from './base.service'
import { ErrorWrapper, ResponseWrapper } from './util'

export class FormService extends BaseService {
  static item_list = [];
  static setItem (form_id, item, value) {
    if ( !this.item_list[form_id] ) {
      this.item_list[form_id] = [];
    }
    if ( !this.item_list[form_id][item] ) {
      this.item_list[form_id][item] = {};
    }
    this.item_list[form_id][item].value = value;

    console.log('add item ' + item);
  }

  static getItemList () {
    return this.item_list;
  }

  static get entity () {
    return 'model'
  }

  static async loadDictionary (model_name, column_name, params1 = {}) {
    /*
    assert.object(params,
      {
        action: 'model',
        do: 'load_dictionary_with_params',
        model_name: model_name,
        columnName: column_name,
      })
     */

    const params = {
      action: 'model',
      do: 'load_dictionary_with_params',
      model_name: model_name,
      columnName: column_name,
      params: this.getItemList()
    };

    try {
      const response = await this.request().post('', params)
      console.log('response');
      console.log(response);
      const data = {
        content: response.data.data,
      }
      return new ResponseWrapper(response, data)
    } catch (error) {
      const message = error.response.data ? error.response.data.error : error.response.statusText
      throw new ErrorWrapper(error, message)
    }
  }


  static async getPostsByUserId (params = {}) {
    assert.object(params, { required: true })
    assert.object(params.filter, { required: true })
    assert.id(params.filter.userId, { required: true })

    try {
      const response = await this.request({ auth: true }).get(`${this.entity}?${this.querystring(params)}`)
      const data = {
        content: response.data.data,
        total: Number(response.headers['x-total-count'])
      }
      return new ResponseWrapper(response, data)
    } catch (error) {
      const message = error.response.data ? error.response.data.error : error.response.statusText
      throw new ErrorWrapper(error, message)
    }
  }
}
