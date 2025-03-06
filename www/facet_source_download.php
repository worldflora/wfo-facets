<?php

    require_once('../include/ExporterFacets.php');
    
    // the downloads on the source page

    // when the page is loaded we must clear the exporter 
    // the modal will create a new one if we need it
    // but we need a clean slate 

    if(isset($_SESSION['exporter'])){
        $exporter = unserialize($_SESSION['exporter']);
        $exporter->deleteSqliteDb();
        unset($_SESSION['exporter']);
        unset($exporter);
    }


?>
<p class="lead">
Here you can download a copy of your list explanded with the classification in the current index.
It is a two stage process. Firstly you generate an export (which can be time consuming) then you 
download a file in the format you require.
</p>

<hr/>
<?php
    // see if the sqlite file exists
    $download_dir = 'downloads/' . $source_id . '/';


    if(file_exists($download_dir) && $files = glob($download_dir  . '*' )){
        echo "<ul>";
        foreach($files as $file){
            echo "<li>";
            echo "<a href=\"$file\">".  basename($file) . "</a>";
            echo "&nbsp;-&nbsp;Generated: " . date ("F d Y @ H:i:s.", filemtime($file));
            echo "</li>";
        }
        echo "</ul>";

        $button_text = "Regenerate download files";
    }else{
        echo "<p>There are no files available to download. Click the button below to generate them.</p>";
        $button_text = "Generate download files";
    }
?>
<hr/>
<p>
<button type="button" data-bs-toggle="modal" data-bs-target="#generateModal" class="btn btn-primary" ><?php echo $button_text ?></button>

<!--  Modal -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-labelledby="generateModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="generateModalLabel">Generate download files</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="generateModalContent">
        Working ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Stop</button>
      </div>
    </div>
  </div>
</div>

</p>

<script>

    function generateFilesPage(){
        const modalContent = document.getElementById('generateModalContent');
        fetch("facet_source_generate_files.php?source_id=" + <?php echo $source_id ?>)
        .then(response => response.json())
        .then(json => {
            modalContent.innerHTML = json.message;
            if(json.finished){
                window.location = "facet_source.php?status=finished&tab=download-tab&source_id=" + <?php echo $source_id ?>;
            }else{ 
                setTimeout( () => { 
                    generateFilesPage();
                }, 10);
            }
            return json;
        })
    }

    // modal dialogue - load content on show
    document.getElementById('generateModal').addEventListener('show.bs.modal', event => {
        generateFilesPage(0);
    })

    // reload page on stop
    document.getElementById('generateModal').addEventListener('hide.bs.modal', event => {
        window.location = "facet_source.php?tab=download-tab&source_id=" + <?php echo $source_id ?>;
    })
</script>
