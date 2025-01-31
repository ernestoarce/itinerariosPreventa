<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Itinerario</title>
  <link rel="stylesheet" href="resources/bootstrap/bootstrap.css">
  <link rel="stylesheet" href="resources/bootstrap/bootstrap-icons.css">
  <link rel="stylesheet" href="resources/toast.css">
</head>
<body>
  <div id="content" v-cloak class="container-fluid p-4">
    <div class="container mb-3">
    <div class="row mb-3 justify-content-center">
      <div class="col-md-3 mb-2">
        <div class="input-group">
          <label class="input-group-text">Entregador:</label>
          <select class="form-select" v-model="formItinerary.rute" @change="getClients">
            <option>TODOS</option>
            <option v-for="v in virtualSellers" :key="v.TOUR_ID">{{ v.TOUR_ID }}</option>
          </select>
        </div>
      </div>

      <div class="col-md-3 mb-2">
        <div class="input-group">
          <label class="input-group-text">Prevendedor:</label>
          <select class="form-select" v-model="filters.virtualManager">
            <option selected></option>
            <option v-for="v in virtualSellers" :key="v.ID">{{ v.ID }}</option>
          </select>
        </div>
      </div>

      <div class="col-md-3 mb-2">
        <div class="input-group">
          <label class="input-group-text">Día:</label>
          <select class="form-select" v-model="filters.day">
            <option selected></option>
            <option>LU</option>
            <option>MA</option>
            <option>MI</option>
            <option>JU</option>
            <option>VI</option>
            <option>SA</option>
          </select>
        </div>
      </div>

      <div class="col-md-3 mb-2">
        <button class="btn btn-primary float-end" @click="guardarEnCRM()" v-if="showGuardarEnCRM" :disabled="loaders.saveCRM">
          <i class="bi bi-arrow-up" v-if="!loaders.saveCRM"></i>
          <div class="spinner-border spinner-border-sm" role="status" v-if="loaders.saveCRM"></div>
          Guardar en CRM
        </button>
      </div>

    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="input-group">
          <input v-model="filters.text" type="text" class="form-control" placeholder="Filtrar por: Código/Nombre/Nom-Comercial/Ruta">
        </div>
      </div>
      <div class="col-md-8 text-end">
        <button class="btn btn-primary mx-2" disabled>
          <i class="me-2 bi bi-geo-alt"></i>
          Ordenar por distancia
        </button>
        <button class="btn btn-danger mx-2" disabled>
          <i class="me-2 bi bi-save"></i>
          Grabar Secuencia
        </button>
        <button class="btn btn-success mx-2" disabled>
          <i class="me-2 bi bi-file-earmark-bar-graph"></i>
          Graficar
        </button>
      </div>
    </div>

    </div>

    <div class="row">
      <small class="col text-end fw-bold mx-2">Registros: {{ filteredClients.length }}</small>
    </div>
    <div class="row">
      <div class="col-12">
        <table class="table table-striped table-sm mb-0 table-hover">
          <thead class="table text-white" style="background-color:rgb(9, 89, 175);">
            <tr>
              <th scope="col" :colspan="showAllColumns ? 12 : 10" class="text-center">Clientes de rutas de preventa</th>
              <th scope="col" colspan="7" class="text-center">Días y ruta virtual que le atenderá</th>
            </tr>
            <tr>
              <th scope="col" class="text-center"></th>
              <th scope="col" class="text-center">
                Orden
                <button class="btn btn-sm p-0" @click="orderClients">
                  <i :class="sortOrder === '' ? 'bi bi-list text-white' : sortOrder === 'asc' ? 'bi bi-sort-alpha-down text-white' : 'bi bi-sort-alpha-up text-white'"></i>
                </button>
              </th>
              <th scope="col" class="text-center">Código</th>
              <th scope="col" class="text-center">Nombre</th>
                <th scope="col" class="text-center">Nom-Comercial</th>
                <th scope="col" class="text-center">Ruta</th>
                <th scope="col" class="text-center">Hora de Visita</th>
                <th scope="col" class="text-center">Tiempo de Visita</th>
                <th scope="col" class="text-center">Tiempo de Traslado</th>
                <th scope="col" v-show="showAllColumns" class="text-center">Direccion</th>
                <th scope="col" v-show="showAllColumns" class="text-center">Telefono</th>
              <th scope="col" class="text-center">
                <button class="btn btn-sm p-0" @click="showAllColumns = !showAllColumns">
                  <i :class="showAllColumns ? 'bi bi-caret-left-fill text-white' : 'bi bi-caret-right-fill text-white'"></i>
                </button>
              </th>
              <th scope="col" class="text-center">Prevendedor</th>
              <th scope="col" class="text-center">LU ({{ totals.lu }})</th>
              <th scope="col" class="text-center">MA ({{ totals.ma }})</th>
              <th scope="col" class="text-center">MI ({{ totals.mi }})</th>
              <th scope="col" class="text-center">JU ({{ totals.ju }})</th>
              <th scope="col" class="text-center">VI ({{ totals.vi }})</th>
              <th scope="col" class="text-center">SA ({{ totals.sa }})</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(i, n) in filteredClients" :key="i.KUNNR">
              <!--<td class="text-center" draggable="true" @dragstart="dragStart($event, i)" @dragover.prevent @drop="drop($event, i)" style="cursor: pointer;">
                <i class="bi bi-grip-vertical" v-if="filters.day && filters.virtualManager && formItinerary.rute !== 'TODOS' && formItinerary.rute"></i>
              </td>-->
              <td></td>
              <td>
                <select v-if="filters.day && filters.virtualManager && formItinerary.rute !== 'TODOS' && formItinerary.rute"
                @change="setOrder(i, 'ORDEN_' + filters.day)"
                class="py-0 form-select form-select-sm" v-model="i['ORDEN_' + filters.day]">
                  <option value="0"></option>
                  <option v-for="v in filteredClients.length" :key="v">{{ v }}</option>
                </select>
              </td>
              <td class="text-center">{{ i.KUNNR }}</td>
              <td>{{ i.NAME1 }}</td>
              <td>{{ i.NAME2 }}</td>
              <td class="text-center">{{ i.SORTL }}</td>
              <td class="text-center"></td>
              <td class="text-center"></td>
              <td class="text-center"></td>
              <td v-show="showAllColumns">{{ i.STRAS }}</td>
              <td v-show="showAllColumns" class="text-center">{{ i.TELF1 }}</td>
              <td></td>
              <td class="text-center">
                <select class="py-0 form-select form-select-sm" v-model="i.PREVENDEDOR" @change="setVirtualItinerary('PREVENDEDOR', i.KUNNR, i.PREVENDEDOR)">
                  <option></option>
                  <option v-for="v in virtualSellers" :key="v.ID">{{ v.ID }}</option>
                </select>
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('LU', i.KUNNR, i.LU)" v-model="i.LU" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('MA', i.KUNNR, i.MA)" v-model="i.MA" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('MI', i.KUNNR, i.MI)" v-model="i.MI" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('JU', i.KUNNR, i.JU)" v-model="i.JU" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('VI', i.KUNNR, i.VI)" v-model="i.VI" :disabled="!i.PREVENDEDOR" />
              </td>
              <td class="text-center">
                <input type="checkbox" @change="setVirtualItinerary('SA', i.KUNNR, i.SA)" v-model="i.SA" :disabled="!i.PREVENDEDOR" />
              </td>
            </tr>
            <tr v-if="filteredClients.length > 0">
              <td :colspan="showAllColumns ? 13 : 11" class="text-center py-0">TOTALES</td>
              <td class="text-center py-0">{{ totals.lu }}</td>
              <td class="text-center py-0">{{ totals.ma }}</td>
              <td class="text-center py-0">{{ totals.mi }}</td>
              <td class="text-center py-0">{{ totals.ju }}</td>
              <td class="text-center py-0">{{ totals.vi }}</td>
              <td class="text-center py-0">{{ totals.sa }}</td>
            </tr>
          </tbody>
        </table>
        <div v-show="loaders.list" class="text-center m-2">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
        <div v-show="filteredClients.length === 0 && !loaders.list" class="alert alert-light text-center" role="alert">
          No se encontraron resultados
        </div>
      </div>
    </div>
  </div>

  <script src="resources/sw2.min.js"></script>
  <script src="resources/vue26.js"></script>
  <script src="resources/axios.min.js"></script>
  <script src="index.js?t=<?= time(); ?>"></script>
  <script src="resources/bootstrap.min.js"></script>
</body>
</html>
