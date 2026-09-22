<link rel="stylesheet" href="<?php echo base_url(); ?>assets/js/jquery-ui/css/smoothness/jquery-ui-1.9.2.custom.css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/table-custom.css" />
<script type="text/javascript" src="<?php echo base_url() ?>assets/js/jquery-ui/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url() ?>assets/js/sweetalert2.all.min.js"></script>
<style>
  select {
    width: 70px;
  }

  /* ===== Filtro de status por checkbox ===== */
  .filtro-status-wrap { position: relative; }
  #btn-filtro-status {
    width: 100%;
    text-align: left;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 30px;
  }
  .painel-status {
    display: none;
    position: absolute;
    z-index: 9999;
    top: 32px;
    left: 0;
    min-width: 230px;
    max-height: 320px;
    overflow-y: auto;
    padding: 8px 10px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, .18);
  }
  .painel-status.aberto { display: block; }
  .painel-status label {
    display: block;
    margin: 0 0 4px;
    font-weight: normal;
    white-space: nowrap;
    cursor: pointer;
  }
  .painel-status label input { margin: 0 6px 0 0; vertical-align: middle; }
  .painel-status .divisor { margin: 6px 0; border-top: 1px solid #eee; }
</style>
<?php
// Lista central de status e suas cores. Para criar um novo status,
// basta acrescentar uma linha aqui (e nos selects de adicionar/editar OS).
$statusCores = [
    'Aberto'             => '#00cd00',
    'Orçamento'          => '#CDB380',
    'Negociação'         => '#AEB404',
    'Aprovado'           => '#808080',
    'Aguardando Peças'   => '#FF7F00',
    'Em Andamento'       => '#436eee',
    'Finalizado'         => '#256',
    'Faturado'           => '#B266FF',
    'Cancelado'          => '#CD0000',
    'Descarte'           => '#795548',
    'Recusado'           => '#e91e63',
    'Recusado/Devolvido' => '#9c27b0',
    'Garantia'           => '#009688',
];

// Status marcados pelo usuário no filtro (aceita array ou valor único).
$statusSelecionados = $this->input->get('status');
if (! is_array($statusSelecionados)) {
    $statusSelecionados = ($statusSelecionados === null || $statusSelecionados === '') ? [] : [$statusSelecionados];
}
$statusSelecionados = array_filter($statusSelecionados, function ($s) {
    return $s !== null && $s !== '';
});
?>
<div class="new122">
    <div class="widget-title" style="margin: -20px 0 0">
            <span class="icon">
                <i class="fas fa-diagnoses"></i>
            </span>
            <h5>Ordens de Serviço</h5>
        </div>
    <div class="span12" style="margin-left: 0">
        <form method="get" action="<?php echo base_url(); ?>index.php/os/gerenciar">
            <?php if ($this->permission->checkPermission($this->session->userdata('permissao'), 'aOs')) { ?>
                <div class="span3">
                    <a href="<?php echo base_url(); ?>index.php/os/adicionar" class="button btn btn-mini btn-success" style="max-width: 160px">
                        <span class="button__icon"><i class='bx bx-plus-circle'></i></span><span class="button__text2">Ordem de Serviço</span></a>
                </div>
            <?php
            } ?>

            <div class="span3">
                <input type="text" name="pesquisa" id="pesquisa" placeholder="Nome do cliente a pesquisar" class="span12" value="<?=set_value('pesquisa')?>">
            </div>
            <div class="span2 filtro-status-wrap">
                <button type="button" id="btn-filtro-status" class="btn btn-mini">
                    <i class='bx bx-filter-alt'></i>
                    <span id="label-filtro-status">Selecione status</span>
                    <span class="caret" style="float:right;margin-top:8px"></span>
                </button>
                <div id="painel-status" class="painel-status">
                    <label><input type="checkbox" id="status-marcar-todos"> <b>Marcar todos</b></label>
                    <div class="divisor"></div>
                    <?php foreach (array_keys($statusCores) as $statusItem) { ?>
                        <label>
                            <input type="checkbox" name="status[]" class="chk-status" value="<?= html_escape($statusItem) ?>" <?= in_array($statusItem, $statusSelecionados) ? 'checked' : '' ?>>
                            <span class="badge" style="background-color: <?= $statusCores[$statusItem] ?>; border-color: <?= $statusCores[$statusItem] ?>"><?= html_escape($statusItem) ?></span>
                        </label>
                    <?php } ?>
                </div>
            </div>

            <div class="span3">
                <input type="text" name="data" autocomplete="off" id="data" placeholder="Data Inicial" class="span6 datepicker" value="<?=html_escape($this->input->get('data'))?>">
                <input type="text" name="data2" autocomplete="off" id="data2" placeholder="Data Final" class="span6 datepicker" value="<?=html_escape($this->input->get('data2'))?>">
            </div>
            <div class="span1">
                <button class="button btn btn-mini btn-warning" style="min-width: 30px">
                    <span class="button__icon"><i class='bx bx-search-alt'></i></span></button>
            </div>
        </form>
    </div>

    <div class="widget-box" style="margin-top: 8px">
        <div class="widget-content nopadding">
            <div class="table-responsive">
                <table class="table table-bordered ">
                    <thead>
                                                <tr>
                            <th>N°</th>
                            <th>Cliente</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Data Inicial</th>
                            <th>Valor Total</th>
                            <th>Desconto</th>
                            <th>Valor com Desconto</th>
                            <th class="ph4">V.T (Faturado)</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$results) {
                            echo '<tr>
                            <td colspan="10">Nenhuma OS Cadastrada</td>
                            </tr>';
                        }

$this->load->model('os_model');
foreach ($results as $r) {
    $dataInicial = date(('d/m/Y'), strtotime($r->dataInicial));
    if ($r->dataFinal != null) {
        $dataFinal = date(('d/m/Y'), strtotime($r->dataFinal));
    } else {
        $dataFinal = "";
    }
    // Só aplica a "visualização padrão" das Configurações quando o usuário
    // não marcou nenhum status no filtro e não fez pesquisa por cliente.
    if (empty($statusSelecionados) && $this->input->get('pesquisa') === null && is_array(json_decode($configuration['os_status_list']))) {
        if (in_array($r->status, json_decode($configuration['os_status_list'])) != true) {
            continue;
        }
    }

    $cor = isset($statusCores[$r->status]) ? $statusCores[$r->status] : '#E0E4CC';

    $vencGarantia = '';

    if ($r->garantia && is_numeric($r->garantia)) {
        $vencGarantia = dateInterval($r->dataFinal, $r->garantia);
    }
    $corGarantia = '';
    if (!empty($vencGarantia)) {
        $dataGarantia = explode('/', $vencGarantia);
        $dataGarantiaFormatada = $dataGarantia[2] . '-' . $dataGarantia[1] . '-' . $dataGarantia[0];
        if (strtotime($dataGarantiaFormatada) >= strtotime(date('d-m-Y'))) {
            $corGarantia = '#4d9c79';
        } else {
            $corGarantia = '#f24c6f';
        }
    } elseif ($r->garantia == "0") {
        $vencGarantia = 'Sem Garantia';
        $corGarantia = '';
    } else {
        $vencGarantia = '';
        $corGarantia = '';
    }

    echo '<tr>';
    echo '<td>' . $r->idOs . '</td>';
    echo '<td class="cli1"><a href="' . base_url() . 'index.php/clientes/visualizar/' . $r->idClientes . '" style="margin-right: 1%">' . $r->nomeCliente . '</a></td>';
    echo '<td>' . $r->marca . '</td>';
    echo '<td>' . $r->modelo . '</td>';
    echo '<td>' . $dataInicial . '</td>';
    echo '<td>R$ ' . number_format($r->totalProdutos + $r->totalServicos, 2, ',', '.') . '</td>';
    echo '<td>R$ ' . number_format(floatval($r->desconto), 2, ',', '.') . '</td>';
    echo '<td>R$ ' . number_format(floatval($r->valor_desconto), 2, ',', '.') . '</td>';
    echo '<td class="ph4">R$ ' . number_format($r->faturado ? floatval($r->valor_desconto) : 0.00, 2, ',', '.') . '</td>';
    echo '<td><span class="badge" style="background-color: ' . $cor . '; border-color: ' . $cor . '">' . $r->status . '</span> </td>';
    echo '<td>';

    $editavel = $this->os_model->isEditable($r->idOs);

    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'vOs')) {
        echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/visualizar/' . $r->idOs . '" class="btn-nwe" title="Ver mais detalhes"><i class="bx bx-show"></i></a>';
        echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/imprimir/' . $r->idOs . '" target="_blank" class="btn-nwe6" title="Imprimir A4"><i class="bx bx-printer bx-xs"></i></a>';
        echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/imprimirTermica/' . $r->idOs . '" target="_blank" class="btn-nwe6" title="Imprimir Não Fiscal"><i class="bx bx-printer bx-xs"></i></a>';
    }
    if ($editavel) {
        echo '<a style="margin-right: 1%" href="' . base_url() . 'index.php/os/editar/' . $r->idOs . '" class="btn-nwe3" title="Editar OS"><i class="bx bx-edit"></i></a>';
    }
    if ($this->permission->checkPermission($this->session->userdata('permissao'), 'dOs') && $editavel) {
        echo '<a href="#modal-excluir" role="button" data-toggle="modal" os="' . $r->idOs . '" class="btn-nwe4" title="Excluir OS"><i class="bx bx-trash-alt"></i></a>  ';
    }
    echo '</td>';
    echo '</tr>';
} ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php echo $this->pagination->create_links(); ?>

    <!-- Modal -->
    <div id="modal-excluir" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <form action="<?php echo base_url() ?>index.php/os/excluir" method="post">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h5 id="myModalLabel">Excluir OS</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idOs" name="id" value="" />
                <h5 style="text-align: center">Deseja realmente excluir esta OS?</h5>
            </div>
            <div class="modal-footer" style="display:flex;justify-content: center">
                <button class="button btn btn-warning" data-dismiss="modal" aria-hidden="true">
                    <span class="button__icon"><i class="bx bx-x"></i></span><span class="button__text2">Cancelar</span></button>
                <button class="button btn btn-danger"><span class="button__icon"><i class='bx bx-trash'></i></span> <span class="button__text2">Excluir</span></button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', 'a', function(event) {
            var os = $(this).attr('os');
            $('#idOs').val(os);
        });
        $(document).on('click', '#excluir-notificacao', function(event) {
            event.preventDefault();
            $.ajax({
                    url: '<?php echo site_url() ?>/os/excluir_notificacao',
                    type: 'GET',
                    dataType: 'json',
                })
                .done(function(data) {
                    if (data.result == true) {
                        Swal.fire({
                            type: "success",
                            title: "Sucesso",
                            text: "Notificação excluída com sucesso."
                        });
                        location.reload();
                    } else {
                        Swal.fire({
                            type: "success",
                            title: "Sucesso",
                            text: "Ocorreu um problema ao tentar exlcuir notificação."
                        });
                    }
                });
        });
        $(".datepicker").datepicker({
            dateFormat: 'dd/mm/yy'
        });

        // ===== Filtro de status por checkbox =====
        function atualizarLabelStatus() {
            var marcados = $('.chk-status:checked');
            var total = $('.chk-status').length;
            if (marcados.length === 0) {
                $('#label-filtro-status').text('Selecione status');
            } else if (marcados.length === 1) {
                $('#label-filtro-status').text(marcados.first().val());
            } else if (marcados.length === total) {
                $('#label-filtro-status').text('Todos os status');
            } else {
                $('#label-filtro-status').text(marcados.length + ' status selecionados');
            }
            $('#status-marcar-todos').prop('checked', marcados.length === total && total > 0);
        }

        $('#btn-filtro-status').on('click', function(e) {
            e.stopPropagation();
            $('#painel-status').toggleClass('aberto');
        });

        $('#painel-status').on('click', function(e) {
            e.stopPropagation();
        });

        $(document).on('click', function() {
            $('#painel-status').removeClass('aberto');
        });

        $('#status-marcar-todos').on('change', function() {
            $('.chk-status').prop('checked', $(this).is(':checked'));
            atualizarLabelStatus();
        });

        $('.chk-status').on('change', atualizarLabelStatus);

        atualizarLabelStatus();
    });
</script>
