    </main>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?= isset($extraJS) ? $extraJS : '' ?>
<style>
@media(max-width:768px){
  #sidebarToggle{display:block !important}
  .sidebar{transform:translateX(-100%);transition:transform 0.3s ease}
  .sidebar.open{transform:translateX(0)}
  .dashboard-main{margin-left:0}
}
</style>
</body>
</html>
