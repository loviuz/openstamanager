<?php

use Modules\TipiIntervento\Tipo;

switch (post('op')) {
    case 'update':
        $tipo->descrizione = post('descrizione');
        $tipo->tempo_standard = post('tempo_standard');

        $tipo->costo_orario = post('costo_orario');
        $tipo->costo_km = post('costo_km');
        $tipo->costo_diritto_chiamata = post('costo_diritto_chiamata');
        $tipo->costo_orario_tecnico = post('costo_orario_tecnico');
        $tipo->costo_km_tecnico = post('costo_km_tecnico');
        $tipo->costo_diritto_chiamata_tecnico = post('costo_diritto_chiamata_tecnico');

        $tipo->save();

        flash()->info(tr('Informazioni tipo intervento salvate correttamente!'));

        break;

    case 'add':
        $codice = post('codice');
        $descrizione = post('descrizione');

        $tipo = Tipo::build($codice, $descrizione);

        $tipo->tempo_standard = post('tempo_standard');

        $tipo->costo_orario = post('costo_orario');
        $tipo->costo_km = post('costo_km');
        $tipo->costo_diritto_chiamata = post('costo_diritto_chiamata');
        $tipo->costo_orario_tecnico = post('costo_orario_tecnico');
        $tipo->costo_km_tecnico = post('costo_km_tecnico');
        $tipo->costo_diritto_chiamata_tecnico = post('costo_diritto_chiamata_tecnico');

        $tipo->save();

        // Fix per impostare i valori inziali a tutti i tecnici
        $tipo->fixTecnici();

        $id_record = $tipo->id;

        flash()->info(tr('Nuovo tipo di intervento aggiunto!'));

        break;

    case 'delete':
        // Permetto eliminazione tipo intervento solo se questo non è utilizzado da nessun'altra parte a gestionale
        $elementi = $dbo->fetchArray('SELECT `in_interventi`.`id_tipo_intervento`  FROM `in_interventi` WHERE `in_interventi`.`id_tipo_intervento` = '.prepare($id_record).'
        UNION
        SELECT `an_anagrafiche`.`id_tipo_intervento_default` AS `id_tipo_intervento` FROM `an_anagrafiche` WHERE `an_anagrafiche`.`id_tipo_intervento_default` = '.prepare($id_record).'
        UNION
        SELECT `co_preventivi`.`id_tipo_intervento` FROM `co_preventivi` WHERE `co_preventivi`.`id_tipo_intervento` = '.prepare($id_record).'
        UNION
        SELECT `co_promemoria`.`id_tipo_intervento` FROM `co_promemoria` WHERE `co_promemoria`.`id_tipo_intervento` = '.prepare($id_record).'
        UNION
        SELECT `in_tariffe`.`id_tipo_intervento` FROM `in_tariffe` WHERE `in_tariffe`.`id_tipo_intervento` = '.prepare($id_record).'
        UNION
        SELECT `in_interventi_tecnici`.`id_tipo_intervento` FROM `in_interventi_tecnici` WHERE `in_interventi_tecnici`.`id_tipo_intervento` = '.prepare($id_record).'
        UNION
        SELECT `co_contratti_tipiintervento`.`id_tipo_intervento` FROM `co_contratti_tipiintervento` WHERE `co_contratti_tipiintervento`.`id_tipo_intervento` = '.prepare($id_record).'
        ORDER BY `idtipointervento`');

        if (empty($elementi)) {
            $query = 'DELETE FROM in_tipiintervento WHERE id_tipo_intervento='.prepare($id_record);
            $dbo->query($query);

            // Elimino anche le tariffe collegate ai vari tecnici
            $query = 'DELETE FROM in_tariffe WHERE id_tipo_intervento='.prepare($id_record);
            $dbo->query($query);

            flash()->info(tr('Tipo di intervento eliminato!'));
            break;
        }

        // no break
    case 'import':
        $values = [
            'costo_ore' => $record['costo_orario'],
            'costo_km' => $record['costo_km'],
            'costo_dirittochiamata' => $record['costo_diritto_chiamata'],
            'costo_ore_tecnico' => $record['costo_orario_tecnico'],
            'costo_km_tecnico' => $record['costo_km_tecnico'],
            'costo_dirittochiamata_tecnico' => $record['costo_diritto_chiamata_tecnico'],
        ];

        $dbo->update('in_tariffe', $values, [
            'idtipointervento' => $id_record,
        ]);

        break;
}
