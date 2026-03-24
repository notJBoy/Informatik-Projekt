                <!-- Fächer Detail View -->
                <div id="subjects" class="view-content" style="display: none;">
                    <div class="content-header">
                        <h1>📚 Fächer</h1>
                        <p>Verwalte deine Fächer und Farben</p>
                    </div>
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">Neues Fach hinzufügen</div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="subjectName" placeholder="Fach eingeben...">
                            <button type="button" id="subjectColorBtn" class="btn-secondary" onclick="openColorModal()">Farbe wählen</button>
                            <input type="hidden" id="subjectColor" value="#1E90FF">
                            <button class="btn-primary" onclick="addSubject()">Hinzufügen</button>
                        </div>
                        <div class="subjects-list" id="subjectsList" style="margin-top: 1.5rem;">
                            <p style="color:var(--color-text-muted);text-align:center;padding:1rem;">Wird geladen...</p>
                        </div>
                        <!-- Modal for color selection -->
                        <div id="colorModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
                            <div class="modal-content" style="background-color:var(--color-bg-secondary); border-radius:8px; width:90%; max-width:500px; max-height:80%; overflow-y:auto;">
                                <div class="modal-header" style="padding:1rem; border-bottom:1px solid var(--color-border); display:flex; justify-content:space-between; align-items:center;">
                                    <h3 style="margin:0;">Farbe auswählen</h3>
                                    <button class="btn-icon" onclick="closeColorModal()">❌</button>
                                </div>
                                <div class="modal-body" style="padding:1rem;">
                                    <div class="color-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(40px, 1fr)); gap:0.5rem;">
                                        <div class="color-option" data-color="#A52A2A" onclick="selectColor('#A52A2A')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#A52A2A;" title="Rotbraun"></div>
                                        <div class="color-option" data-color="#FF6347" onclick="selectColor('#FF6347')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FF6347;" title="Rot"></div>
                                        <div class="color-option" data-color="#DC143C" onclick="selectColor('#DC143C')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#DC143C;" title="Crimson"></div>
                                        <div class="color-option" data-color="#FFA500" onclick="selectColor('#FFA500')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FFA500;" title="Dunkelorange"></div>
                                        <div class="color-option" data-color="#FF8C00" onclick="selectColor('#FF8C00')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FF8C00;" title="Orange"></div>
                                        <div class="color-option" data-color="#FFD700" onclick="selectColor('#FFD700')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FFD700;" title="Gelb"></div>
                                        <div class="color-option" data-color="#FFFF33" onclick="selectColor('#FFFF33')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FFFF33;" title="Neongelb"></div>
                                        <div class="color-option" data-color="#FFCC00" onclick="selectColor('#FFCC00')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FFCC00;" title="Gold"></div>
                                        <div class="color-option" data-color="#F5F5DC" onclick="selectColor('#F5F5DC')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#F5F5DC;" title="Beige"></div>
                                        <div class="color-option" data-color="#D2691E" onclick="selectColor('#D2691E')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#D2691E;" title="Braun"></div>
                                        <div class="color-option" data-color="#000000" onclick="selectColor('#000000')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#000000;" title="Schwarz"></div>
                                        <div class="color-option" data-color="#808080" onclick="selectColor('#808080')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#808080;" title="Grau"></div>
                                        <div class="color-option" data-color="#D3D3D3" onclick="selectColor('#D3D3D3')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#D3D3D3;" title="Hellgrau"></div>
                                        <div class="color-option" data-color="#C0C0C0" onclick="selectColor('#C0C0C0')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#C0C0C0;" title="Silber"></div>
                                        <div class="color-option" data-color="#FFFFFF" onclick="selectColor('#FFFFFF')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FFFFFF;" title="Weiß"></div>
                                        <div class="color-option" data-color="#000080" onclick="selectColor('#000080')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#000080;" title="Dunkelblau"></div>
                                        <div class="color-option" data-color="#1E90FF" onclick="selectColor('#1E90FF')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#1E90FF;" title="Blau"></div>
                                        <div class="color-option" data-color="#87CEFA" onclick="selectColor('#87CEFA')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#87CEFA;" title="Hellblau"></div>
                                        <div class="color-option" data-color="#008000" onclick="selectColor('#008000')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#008000;" title="Dunkelgrün"></div>
                                        <div class="color-option" data-color="#32CD32" onclick="selectColor('#32CD32')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#32CD32;" title="Grün"></div>
                                        <div class="color-option" data-color="#98FF98" onclick="selectColor('#98FF98')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#98FF98;" title="Mintgrün"></div>
                                        <div class="color-option" data-color="#40E0D0" onclick="selectColor('#40E0D0')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#40E0D0;" title="Türkis"></div>
                                        <div class="color-option" data-color="#00CED1" onclick="selectColor('#00CED1')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#00CED1;" title="Dunkeltürkis"></div>
                                        <div class="color-option" data-color="#8A2BE2" onclick="selectColor('#8A2BE2')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#8A2BE2;" title="Lila"></div>
                                        <div class="color-option" data-color="#800080" onclick="selectColor('#800080')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#800080;" title="Dunkellila"></div>
                                        <div class="color-option" data-color="#8B008B" onclick="selectColor('#8B008B')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#8B008B;" title="Violett"></div>
                                        <div class="color-option" data-color="#FF69B4" onclick="selectColor('#FF69B4')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FF69B4;" title="Pink"></div>
                                        <div class="color-option" data-color="#FF1493" onclick="selectColor('#FF1493')" style="width:40px; height:40px; border:2px solid var(--color-border); border-radius:4px; cursor:pointer; background-color:#FF1493;" title="Neonpink"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal for subject details -->
                        <div id="detailsModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
                            <div class="modal-content" style="background-color:var(--color-bg-secondary); border-radius:12px; width:90%; max-width:700px; max-height:80%; overflow-y:auto; box-shadow:0 10px 30px rgba(0,0,0,0.3); border:1px solid var(--color-border);">
                                <div class="modal-header" style="padding:1.5rem; border-bottom:1px solid var(--color-border); display:flex; justify-content:space-between; align-items:center; background:linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-hover) 100%); color:white; border-radius:12px 12px 0 0;">
                                    <h3 id="detailsTitle" style="margin:0; font-size:1.2rem;">Fach-Details</h3>
                                    <button class="btn-icon" onclick="closeDetailsModal()" style="background:none; border:none; color:white; font-size:1.5rem; cursor:pointer;">✕</button>
                                </div>
                                <div class="modal-body" id="detailsContent" style="padding:1.5rem; line-height:1.6;">
                                    <!-- Content will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
